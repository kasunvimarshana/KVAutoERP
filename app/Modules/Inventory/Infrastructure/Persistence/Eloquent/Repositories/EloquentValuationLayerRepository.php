<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\ValuationLayerModel;
class EloquentValuationLayerRepository implements ValuationLayerRepositoryInterface {
    public function __construct(private readonly ValuationLayerModel $model) {}
    private function base(int $tenantId, int $productId, int $warehouseId) { return $this->model->newQuery()->where('tenant_id',$tenantId)->where('product_id',$productId)->where('warehouse_id',$warehouseId)->where('remaining_quantity','>',0); }
    public function findActiveByProduct(int $tenantId, int $productId, int $warehouseId): array { return $this->base($tenantId,$productId,$warehouseId)->orderBy('received_at')->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function findActiveByProductDesc(int $tenantId, int $productId, int $warehouseId): array { return $this->base($tenantId,$productId,$warehouseId)->orderByDesc('received_at')->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function findActiveByExpiry(int $tenantId, int $productId, int $warehouseId): array { return $this->base($tenantId,$productId,$warehouseId)->orderByRaw('COALESCE(expiry_date, "9999-12-31")')->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function save(ValuationLayer $l): ValuationLayer {
        $m=$l->getId()?$this->model->newQuery()->findOrFail($l->getId()):new ValuationLayerModel();
        $m->tenant_id=$l->getTenantId();$m->product_id=$l->getProductId();$m->variant_id=$l->getVariantId();$m->warehouse_id=$l->getWarehouseId();$m->quantity=$l->getQuantity();$m->remaining_quantity=$l->getRemainingQuantity();$m->unit_cost=$l->getUnitCost();$m->received_at=$l->getReceivedAt()->format('Y-m-d H:i:s');$m->batch_number=$l->getBatchNumber();$m->lot_number=$l->getLotNumber();$m->expiry_date=$l->getExpiryDate()?->format('Y-m-d');
        $m->save(); return $this->toEntity($m);
    }
    private function toEntity(ValuationLayerModel $m): ValuationLayer { return new ValuationLayer($m->id,$m->tenant_id,$m->product_id,$m->variant_id,$m->warehouse_id,(float)$m->quantity,(float)$m->remaining_quantity,(float)$m->unit_cost,new \DateTimeImmutable($m->received_at->toDateTimeString()),$m->batch_number,$m->lot_number,$m->expiry_date?new \DateTimeImmutable($m->expiry_date->toDateString()):null); }
}
