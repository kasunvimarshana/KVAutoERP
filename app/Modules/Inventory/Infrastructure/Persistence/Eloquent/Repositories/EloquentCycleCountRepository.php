<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountModel;

class EloquentCycleCountRepository implements CycleCountRepositoryInterface
{
    public function __construct(
        private readonly CycleCountModel $model,
    ) {}

    public function findById(int $id): ?CycleCount
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByWarehouse(int $tenantId, int $warehouseId, ?string $status): array
    {
        $query = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('warehouse_id', $warehouseId);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('created_at')
            ->get()
            ->map(fn (CycleCountModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): CycleCount
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?CycleCount
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    private function toEntity(CycleCountModel $model): CycleCount
    {
        return new CycleCount(
            id: $model->id,
            tenantId: $model->tenant_id,
            warehouseId: $model->warehouse_id,
            status: $model->status,
            startedAt: $model->started_at,
            completedAt: $model->completed_at,
            createdBy: $model->created_by,
            notes: $model->notes,
            createdAt: $model->created_at,
        );
    }
}
