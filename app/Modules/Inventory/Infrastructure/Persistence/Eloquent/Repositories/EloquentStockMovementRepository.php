<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
class EloquentStockMovementRepository implements StockMovementRepositoryInterface {
    public function __construct(private readonly StockMovementModel $model) {}
    public function findByProduct(int $tenantId, int $productId): array { return $this->model->newQuery()->where('tenant_id',$tenantId)->where('product_id',$productId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function findByWarehouse(int $tenantId, int $warehouseId): array { return $this->model->newQuery()->where('tenant_id',$tenantId)->where('warehouse_id',$warehouseId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function save(StockMovement $s): StockMovement {
        $m=$s->getId()?$this->model->newQuery()->findOrFail($s->getId()):new StockMovementModel();
        $m->tenant_id=$s->getTenantId();$m->product_id=$s->getProductId();$m->variant_id=$s->getVariantId();$m->warehouse_id=$s->getWarehouseId();$m->location_id=$s->getLocationId();$m->type=$s->getType();$m->quantity=$s->getQuantity();$m->unit_cost=$s->getUnitCost();$m->reference=$s->getReference();$m->batch_number=$s->getBatchNumber();$m->lot_number=$s->getLotNumber();$m->serial_number=$s->getSerialNumber();$m->expiry_date=$s->getExpiryDate()?->format('Y-m-d');$m->moved_at=$s->getMovedAt()->format('Y-m-d H:i:s');$m->notes=$s->getNotes();
        $m->save(); return $this->toEntity($m);
    }
    private function toEntity(StockMovementModel $m): StockMovement { return new StockMovement($m->id,$m->tenant_id,$m->product_id,$m->variant_id,$m->warehouse_id,$m->location_id,$m->type,(float)$m->quantity,(float)$m->unit_cost,$m->reference,$m->batch_number,$m->lot_number,$m->serial_number,$m->expiry_date?new \DateTimeImmutable($m->expiry_date->toDateString()):null,new \DateTimeImmutable($m->moved_at->toDateTimeString()),$m->notes); }
}
