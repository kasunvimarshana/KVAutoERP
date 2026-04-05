<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\InventoryAdjustment;
use Modules\Inventory\Domain\Entities\InventoryAdjustmentLine;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryAdjustmentRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryAdjustmentLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryAdjustmentModel;

class EloquentInventoryAdjustmentRepository implements InventoryAdjustmentRepositoryInterface
{
    public function __construct(
        private readonly InventoryAdjustmentModel $model,
        private readonly InventoryAdjustmentLineModel $lineModel,
    ) {}

    public function findById(int $id, int $tenantId): ?InventoryAdjustment
    {
        $record = $this->query($tenantId)->where('id', $id)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function findByNumber(string $adjustmentNumber, int $tenantId): ?InventoryAdjustment
    {
        $record = $this->query($tenantId)
            ->where('adjustment_number', $adjustmentNumber)
            ->first();
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

    public function create(array $data): InventoryAdjustment
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toDomain($record);
    }

    public function update(int $id, array $data): InventoryAdjustment
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

    public function findLines(int $adjustmentId, int $tenantId): array
    {
        return $this->lineModel->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('adjustment_id', $adjustmentId)
            ->get()
            ->map(fn ($r) => $this->toLineDomain($r))
            ->all();
    }

    public function createLine(array $data): InventoryAdjustmentLine
    {
        $record = $this->lineModel->newQuery()->create($data);
        return $this->toLineDomain($record);
    }

    public function deleteLine(int $lineId, int $tenantId): bool
    {
        return (bool) $this->lineModel->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('id', $lineId)
            ->delete();
    }

    private function query(int $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId);
    }

    private function toDomain(InventoryAdjustmentModel $m): InventoryAdjustment
    {
        return new InventoryAdjustment(
            id:               $m->id,
            tenantId:         $m->tenant_id,
            adjustmentNumber: $m->adjustment_number,
            date:             new \DateTimeImmutable($m->date->toDateString()),
            locationId:       $m->location_id,
            status:           $m->status,
            reason:           $m->reason,
            notes:            $m->notes,
            createdBy:        $m->created_by,
            approvedBy:       $m->approved_by,
            createdAt:        $m->created_at
                ? new \DateTimeImmutable($m->created_at->toDateTimeString())
                : null,
            updatedAt:        $m->updated_at
                ? new \DateTimeImmutable($m->updated_at->toDateTimeString())
                : null,
        );
    }

    private function toLineDomain(InventoryAdjustmentLineModel $m): InventoryAdjustmentLine
    {
        return new InventoryAdjustmentLine(
            id:               $m->id,
            tenantId:         $m->tenant_id,
            adjustmentId:     $m->adjustment_id,
            productId:        $m->product_id,
            variantId:        $m->variant_id,
            expectedQuantity: (float) $m->expected_quantity,
            actualQuantity:   (float) $m->actual_quantity,
            unitCost:         (float) $m->unit_cost,
            batchLotId:       $m->batch_lot_id,
        );
    }
}
