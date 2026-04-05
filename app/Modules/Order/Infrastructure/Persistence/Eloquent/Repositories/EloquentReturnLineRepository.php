<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Order\Domain\Entities\ReturnLine;
use Modules\Order\Domain\RepositoryInterfaces\ReturnLineRepositoryInterface;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\ReturnLineModel;

class EloquentReturnLineRepository implements ReturnLineRepositoryInterface
{
    public function __construct(
        private readonly ReturnLineModel $model,
    ) {}

    public function findByReturn(int $returnId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('return_id', $returnId)
            ->get()
            ->map(fn (ReturnLineModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): ReturnLine
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?ReturnLine
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);
        if ($record === null) {
            return null;
        }
        $record->fill($data)->save();
        return $this->toEntity($record->fresh());
    }

    public function bulkCreate(array $lines): array
    {
        return array_map(fn (array $line) => $this->create($line), $lines);
    }

    private function toEntity(ReturnLineModel $model): ReturnLine
    {
        return new ReturnLine(
            id: $model->id,
            returnId: $model->return_id,
            orderLineId: $model->order_line_id,
            productId: $model->product_id,
            variantId: $model->variant_id,
            batchId: $model->batch_id,
            quantity: (float) $model->quantity,
            condition: $model->condition,
            unitPrice: (float) $model->unit_price,
            restockToWarehouseId: $model->restock_to_warehouse_id,
            restockToLocationId: $model->restock_to_location_id,
            shouldRestock: (bool) $model->should_restock,
            notes: $model->notes,
        );
    }
}
