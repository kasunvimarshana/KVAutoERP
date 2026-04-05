<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\Entities\CycleCountLine;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountModel;

final class EloquentCycleCountRepository implements CycleCountRepositoryInterface
{
    public function __construct(
        private readonly CycleCountModel $model,
        private readonly CycleCountLineModel $lineModel,
    ) {}

    public function findById(int $id): ?CycleCount
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (CycleCountModel $m) => $this->toEntity($m));
    }

    public function findByWarehouse(int $warehouseId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (CycleCountModel $m) => $this->toEntity($m));
    }

    public function create(array $data): CycleCount
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?CycleCount
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function addLine(int $cycleCountId, array $lineData): CycleCountLine
    {
        $lineData['cycle_count_id'] = $cycleCountId;
        $record = $this->lineModel->newQueryWithoutScopes()->create($lineData);

        return $this->toLineEntity($record);
    }

    public function getLines(int $cycleCountId): Collection
    {
        return $this->lineModel->newQueryWithoutScopes()
            ->where('cycle_count_id', $cycleCountId)
            ->get()
            ->map(fn (CycleCountLineModel $m) => $this->toLineEntity($m));
    }

    private function toEntity(CycleCountModel $model): CycleCount
    {
        $scheduledDate = null;
        if ($model->scheduled_date !== null) {
            $scheduledDate = new \DateTimeImmutable($model->scheduled_date->toDateTimeString());
        }

        $completedDate = null;
        if ($model->completed_date !== null) {
            $completedDate = new \DateTimeImmutable($model->completed_date->toDateTimeString());
        }

        return new CycleCount(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            warehouseId: (int) $model->warehouse_id,
            locationId: $model->location_id !== null ? (int) $model->location_id : null,
            referenceNo: (string) $model->reference_no,
            status: (string) $model->status,
            scheduledDate: $scheduledDate,
            completedDate: $completedDate,
            createdBy: $model->created_by !== null ? (int) $model->created_by : null,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }

    private function toLineEntity(CycleCountLineModel $model): CycleCountLine
    {
        return new CycleCountLine(
            id: (int) $model->id,
            cycleCountId: (int) $model->cycle_count_id,
            productId: (int) $model->product_id,
            productVariantId: $model->product_variant_id !== null ? (int) $model->product_variant_id : null,
            systemQty: (float) $model->system_qty,
            countedQty: $model->counted_qty !== null ? (float) $model->counted_qty : null,
            variance: $model->variance !== null ? (float) $model->variance : null,
            notes: $model->notes !== null ? (string) $model->notes : null,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
