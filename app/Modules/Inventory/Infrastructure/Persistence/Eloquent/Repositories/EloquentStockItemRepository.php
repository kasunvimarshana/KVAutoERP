<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Inventory\Domain\Entities\StockItem;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockItemModel;
class EloquentStockItemRepository implements StockItemRepositoryInterface {
    public function __construct(private readonly StockItemModel $model) {}
    public function findByProduct(int $tenantId, int $productId): array { return $this->model->newQuery()->where('tenant_id',$tenantId)->where('product_id',$productId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function findByWarehouse(int $tenantId, int $warehouseId): array { return $this->model->newQuery()->where('tenant_id',$tenantId)->where('warehouse_id',$warehouseId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function findByProductAndWarehouse(int $tenantId, int $productId, int $warehouseId): ?StockItem { $m=$this->model->newQuery()->where('tenant_id',$tenantId)->where('product_id',$productId)->where('warehouse_id',$warehouseId)->first(); return $m?$this->toEntity($m):null; }
    public function save(StockItem $s): StockItem {
        $m=$s->getId()?$this->model->newQuery()->findOrFail($s->getId()):new StockItemModel();
        $m->tenant_id=$s->getTenantId();$m->product_id=$s->getProductId();$m->variant_id=$s->getVariantId();$m->warehouse_id=$s->getWarehouseId();$m->location_id=$s->getLocationId();$m->quantity=$s->getQuantity();$m->reserved_quantity=$s->getReservedQuantity();$m->available_quantity=$s->getAvailableQuantity();$m->unit=$s->getUnit();
        $m->save(); return $this->toEntity($m);
    }
    private function toEntity(StockItemModel $m): StockItem { return new StockItem($m->id,$m->tenant_id,$m->product_id,$m->variant_id,$m->warehouse_id,$m->location_id,(float)$m->quantity,(float)$m->reserved_quantity,(float)$m->available_quantity,$m->unit); }
}
