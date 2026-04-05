<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Order\Domain\Entities\OrderLine;
use Modules\Order\Domain\RepositoryInterfaces\OrderLineRepositoryInterface;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderLineModel;

class EloquentOrderLineRepository implements OrderLineRepositoryInterface
{
    public function __construct(
        private readonly OrderLineModel $model,
    ) {}

    public function findByOrder(int $orderId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('order_id', $orderId)
            ->get()
            ->map(fn (OrderLineModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): OrderLine
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?OrderLine
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

    public function bulkCreate(array $lines): array
    {
        return array_map(fn (array $line) => $this->create($line), $lines);
    }

    private function toEntity(OrderLineModel $model): OrderLine
    {
        return new OrderLine(
            id: $model->id,
            orderId: $model->order_id,
            productId: $model->product_id,
            variantId: $model->variant_id,
            description: $model->description,
            quantity: (float) $model->quantity,
            unitPrice: (float) $model->unit_price,
            discountAmount: (float) $model->discount_amount,
            taxAmount: (float) $model->tax_amount,
            taxGroupId: $model->tax_group_id,
            totalAmount: (float) $model->total_amount,
            warehouseId: $model->warehouse_id,
            locationId: $model->location_id,
            batchId: $model->batch_id,
            notes: $model->notes,
            metadata: $model->metadata ?? [],
        );
    }
}
