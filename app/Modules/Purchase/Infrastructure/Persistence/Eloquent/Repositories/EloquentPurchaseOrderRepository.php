<?php

declare(strict_types=1);

namespace Modules\Purchase\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Purchase\Domain\Entities\PurchaseOrder;
use Modules\Purchase\Domain\Entities\PurchaseOrderLine;
use Modules\Purchase\Domain\RepositoryInterfaces\PurchaseOrderRepositoryInterface;
use Modules\Purchase\Infrastructure\Persistence\Eloquent\Models\PurchaseOrderLineModel;
use Modules\Purchase\Infrastructure\Persistence\Eloquent\Models\PurchaseOrderModel;

class EloquentPurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{
    public function __construct(
        private readonly PurchaseOrderModel $model,
        private readonly PurchaseOrderLineModel $lineModel,
    ) {}

    public function findById(int $id): ?PurchaseOrder
    {
        $m = $this->model->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->where('tenant_id', $tenantId)->get()->map(fn ($m) => $this->toEntity($m));
    }

    public function findByStatus(int $tenantId, string $status): Collection
    {
        return $this->model->where('tenant_id', $tenantId)->where('status', $status)->get()->map(fn ($m) => $this->toEntity($m));
    }

    public function create(array $data): PurchaseOrder
    {
        return $this->toEntity($this->model->create($data));
    }

    public function update(int $id, array $data): PurchaseOrder
    {
        $m = $this->model->findOrFail($id);
        $m->update($data);
        return $this->toEntity($m->fresh());
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->findOrFail($id)->delete();
    }

    public function addLine(int $orderId, array $data): PurchaseOrderLine
    {
        $data['purchase_order_id'] = $orderId;
        return $this->toLineEntity($this->lineModel->create($data));
    }

    public function updateLine(int $lineId, array $data): PurchaseOrderLine
    {
        $m = $this->lineModel->findOrFail($lineId);
        $m->update($data);
        return $this->toLineEntity($m->fresh());
    }

    public function removeLine(int $lineId): bool
    {
        return (bool) $this->lineModel->findOrFail($lineId)->delete();
    }

    public function getLines(int $orderId): Collection
    {
        return $this->lineModel->where('purchase_order_id', $orderId)->get()->map(fn ($m) => $this->toLineEntity($m));
    }

    private function toEntity(PurchaseOrderModel $m): PurchaseOrder
    {
        return new PurchaseOrder(
            id: $m->id,
            tenantId: $m->tenant_id,
            contactId: $m->contact_id,
            referenceNo: $m->reference_no,
            orderDate: $m->order_date,
            warehouseId: $m->warehouse_id,
            status: $m->status,
            currencyCode: $m->currency_code,
            exchangeRate: (float) $m->exchange_rate,
            subtotal: (float) $m->subtotal,
            discountAmount: (float) $m->discount_amount,
            taxAmount: (float) $m->tax_amount,
            total: (float) $m->total,
            notes: $m->notes,
            expectedDate: $m->expected_date,
            createdBy: $m->created_by,
            createdAt: $m->created_at,
            updatedAt: $m->updated_at,
        );
    }

    private function toLineEntity(PurchaseOrderLineModel $m): PurchaseOrderLine
    {
        return new PurchaseOrderLine(
            id: $m->id,
            purchaseOrderId: $m->purchase_order_id,
            productId: $m->product_id,
            productVariantId: $m->product_variant_id,
            description: $m->description,
            quantity: (float) $m->quantity,
            unitPrice: (float) $m->unit_price,
            discountRate: (float) $m->discount_rate,
            taxRate: (float) $m->tax_rate,
            totalPrice: (float) $m->total_price,
            receivedQty: (float) $m->received_qty,
            unitOfMeasure: $m->unit_of_measure,
            notes: $m->notes,
        );
    }
}
