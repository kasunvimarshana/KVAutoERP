<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\CycleCountLine;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountLineRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountLineModel;

class EloquentCycleCountLineRepository implements CycleCountLineRepositoryInterface
{
    public function __construct(
        private readonly CycleCountLineModel $model,
    ) {}

    public function findByCycleCount(int $cycleCountId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('cycle_count_id', $cycleCountId)
            ->get()
            ->map(fn (CycleCountLineModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): CycleCountLine
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?CycleCountLine
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function bulkCreate(array $rows): void
    {
        foreach (array_chunk($rows, 500) as $chunk) {
            $this->model->newQuery()->insert($chunk);
        }
    }

    private function toEntity(CycleCountLineModel $model): CycleCountLine
    {
        return new CycleCountLine(
            id: $model->id,
            cycleCountId: $model->cycle_count_id,
            productId: $model->product_id,
            variantId: $model->variant_id,
            locationId: $model->location_id,
            batchId: $model->batch_id,
            expectedQuantity: (float) $model->expected_quantity,
            countedQuantity: $model->counted_quantity !== null ? (float) $model->counted_quantity : null,
            variance: $model->variance !== null ? (float) $model->variance : null,
            status: $model->status,
            notes: $model->notes,
        );
    }
}
