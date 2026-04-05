<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\InventoryAdjustment;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryAdjustmentRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryAdjustmentModel;

class EloquentInventoryAdjustmentRepository implements InventoryAdjustmentRepositoryInterface
{
    public function __construct(
        private readonly InventoryAdjustmentModel $model,
    ) {}

    public function findById(int $id): ?InventoryAdjustment
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function create(array $data): InventoryAdjustment
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?InventoryAdjustment
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function findByWarehouse(int $tenantId, int $warehouseId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (InventoryAdjustmentModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(InventoryAdjustmentModel $model): InventoryAdjustment
    {
        return new InventoryAdjustment(
            id: $model->id,
            tenantId: $model->tenant_id,
            warehouseId: $model->warehouse_id,
            status: $model->status,
            reason: $model->reason,
            adjustedBy: $model->adjusted_by,
            approvedBy: $model->approved_by,
            appliedAt: $model->applied_at,
            notes: $model->notes,
            createdAt: $model->created_at,
        );
    }
}
