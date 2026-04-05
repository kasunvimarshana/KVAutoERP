<?php declare(strict_types=1);
namespace Modules\Order\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Order\Domain\Entities\Order;
use Modules\Order\Domain\Entities\OrderLine;
use Modules\Order\Domain\RepositoryInterfaces\OrderRepositoryInterface;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderLineModel;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderModel;
class EloquentOrderRepository implements OrderRepositoryInterface {
    public function __construct(private readonly OrderModel $model, private readonly OrderLineModel $lineModel) {}
    public function findById(int $id): ?Order { $m=$this->model->newQuery()->find($id); if(!$m) return null; $lines=$this->findLinesByOrder($id); $o=$this->toEntity($m); $o->setLines($lines); return $o; }
    public function findByNumber(int $tenantId, string $orderNumber): ?Order { $m=$this->model->newQuery()->where('tenant_id',$tenantId)->where('order_number',$orderNumber)->first(); return $m?$this->toEntity($m):null; }
    public function findByTenant(int $tenantId, ?string $type=null, ?string $status=null): array {
        $q=$this->model->newQuery()->where('tenant_id',$tenantId);
        if($type) $q->where('type',$type);
        if($status) $q->where('status',$status);
        return $q->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(Order $o): Order {
        $m=$o->getId()?$this->model->newQuery()->findOrFail($o->getId()):new OrderModel();
        $m->tenant_id=$o->getTenantId();$m->order_number=$o->getOrderNumber();$m->type=$o->getType();$m->status=$o->getStatus();$m->party_id=$o->getPartyId();$m->warehouse_id=$o->getWarehouseId();$m->order_date=$o->getOrderDate()->format('Y-m-d');$m->currency=$o->getCurrency();$m->subtotal=$o->getSubtotal();$m->tax_amount=$o->getTaxAmount();$m->discount_amount=$o->getDiscountAmount();$m->total_amount=$o->getTotalAmount();$m->notes=$o->getNotes();
        $m->save(); return $this->toEntity($m);
    }
    public function saveLine(OrderLine $l): OrderLine {
        $m=$l->getId()?$this->lineModel->newQuery()->findOrFail($l->getId()):new OrderLineModel();
        $m->order_id=$l->getOrderId();$m->product_id=$l->getProductId();$m->variant_id=$l->getVariantId();$m->quantity=$l->getQuantity();$m->unit_price=$l->getUnitPrice();$m->tax_amount=$l->getTaxAmount();$m->discount_amount=$l->getDiscountAmount();$m->line_total=$l->getLineTotal();$m->batch_number=$l->getBatchNumber();$m->lot_number=$l->getLotNumber();$m->serial_number=$l->getSerialNumber();
        $m->save(); return new OrderLine($m->id,$m->order_id,$m->product_id,$m->variant_id,(float)$m->quantity,(float)$m->unit_price,(float)$m->tax_amount,(float)$m->discount_amount,(float)$m->line_total,$m->batch_number,$m->lot_number,$m->serial_number);
    }
    public function findLinesByOrder(int $orderId): array { return $this->lineModel->newQuery()->where('order_id',$orderId)->get()->map(fn($m)=>new OrderLine($m->id,$m->order_id,$m->product_id,$m->variant_id,(float)$m->quantity,(float)$m->unit_price,(float)$m->tax_amount,(float)$m->discount_amount,(float)$m->line_total,$m->batch_number,$m->lot_number,$m->serial_number))->all(); }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(OrderModel $m): Order { return new Order($m->id,$m->tenant_id,$m->order_number,$m->type,$m->status,$m->party_id,$m->warehouse_id,new \DateTimeImmutable($m->order_date->toDateString()),$m->currency,(float)$m->subtotal,(float)$m->tax_amount,(float)$m->discount_amount,(float)$m->total_amount,$m->notes); }
}
