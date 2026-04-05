<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\Entities\CycleCountLine;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountModel;

class EloquentCycleCountRepository implements CycleCountRepositoryInterface
{
    public function __construct(
        private readonly CycleCountModel $model,
        private readonly CycleCountLineModel $lineModel,
    ) {}

    public function findById(int $id, int $tenantId): ?CycleCount
    {
        $record = $this->query($tenantId)->where('id', $id)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->query($tenantId)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function create(array $data): CycleCount
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toDomain($record);
    }

    public function update(int $id, array $data): CycleCount
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->firstOrFail();
        $record->update($data);
        return $this->toDomain($record->fresh());
    }

    public function delete(int $id, int $tenantId): bool
    {
        return (bool) $this->query($tenantId)->where('id', $id)->delete();
    }

    public function findLines(int $cycleCountId, int $tenantId): array
    {
        return $this->lineModel->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('cycle_count_id', $cycleCountId)
            ->get()
            ->map(fn ($r) => $this->toLineDomain($r))
            ->all();
    }

    public function createLine(array $data): CycleCountLine
    {
        $record = $this->lineModel->newQuery()->create($data);
        return $this->toLineDomain($record);
    }

    public function updateLine(int $lineId, array $data): CycleCountLine
    {
        $record = $this->lineModel->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $lineId)
            ->firstOrFail();
        $record->update($data);
        return $this->toLineDomain($record->fresh());
    }

    private function query(int $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId);
    }

    private function toDomain(CycleCountModel $m): CycleCount
    {
        return new CycleCount(
            id:          $m->id,
            tenantId:    $m->tenant_id,
            countNumber: $m->count_number,
            locationId:  $m->location_id,
            status:      $m->status,
            startedAt:   $m->started_at
                ? new \DateTimeImmutable($m->started_at->toDateTimeString())
                : null,
            completedAt: $m->completed_at
                ? new \DateTimeImmutable($m->completed_at->toDateTimeString())
                : null,
            createdBy:   $m->created_by,
            createdAt:   $m->created_at
                ? new \DateTimeImmutable($m->created_at->toDateTimeString())
                : null,
            updatedAt:   $m->updated_at
                ? new \DateTimeImmutable($m->updated_at->toDateTimeString())
                : null,
        );
    }

    private function toLineDomain(CycleCountLineModel $m): CycleCountLine
    {
        return new CycleCountLine(
            id:              $m->id,
            tenantId:        $m->tenant_id,
            cycleCountId:    $m->cycle_count_id,
            productId:       $m->product_id,
            variantId:       $m->variant_id,
            systemQuantity:  (float) $m->system_quantity,
            countedQuantity: $m->counted_quantity !== null ? (float) $m->counted_quantity : null,
            batchLotId:      $m->batch_lot_id,
        );
    }
}
