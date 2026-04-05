<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Order\Domain\Entities\Order;
use Modules\Order\Domain\RepositoryInterfaces\OrderRepositoryInterface;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderModel;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly OrderModel $model,
    ) {}

    public function findById(int $id): ?Order
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);
        return $record ? $this->toEntity($record) : null;
    }

    public function findByNumber(int $tenantId, string $orderNumber): ?Order
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('order_number', $orderNumber)
            ->first();
        return $record ? $this->toEntity($record) : null;
    }

    public function findByStatus(int $tenantId, string $status): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->get()
            ->map(fn (OrderModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByContact(int $tenantId, int $contactId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('contact_id', $contactId)
            ->get()
            ->map(fn (OrderModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByType(int $tenantId, string $type): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->get()
            ->map(fn (OrderModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Order
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Order
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);
        if ($record === null) {
            return null;
        }
        $record->fill($data)->save();
        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);
        return $record !== null && (bool) $record->delete();
    }

    public function updateStatus(int $id, string $status): ?Order
    {
        return $this->update($id, ['status' => $status]);
    }

    private function toEntity(OrderModel $model): Order
    {
        return new Order(
            id: $model->id,
            tenantId: $model->tenant_id,
            orderNumber: $model->order_number,
            type: $model->type,
            status: $model->status,
            contactId: $model->contact_id,
            warehouseId: $model->warehouse_id,
            currency: $model->currency,
            subtotal: (float) $model->subtotal,
            discountAmount: (float) $model->discount_amount,
            taxAmount: (float) $model->tax_amount,
            shippingAmount: (float) $model->shipping_amount,
            totalAmount: (float) $model->total_amount,
            notes: $model->notes,
            shippingAddress: $model->shipping_address,
            billingAddress: $model->billing_address,
            createdBy: $model->created_by,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
