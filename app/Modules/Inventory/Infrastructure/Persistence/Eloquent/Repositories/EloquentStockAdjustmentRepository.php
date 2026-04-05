<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\StockAdjustment;
use Modules\Inventory\Domain\Entities\StockAdjustmentLine;
use Modules\Inventory\Domain\RepositoryInterfaces\StockAdjustmentRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockAdjustmentLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockAdjustmentModel;

final class EloquentStockAdjustmentRepository implements StockAdjustmentRepositoryInterface
{
    public function __construct(
        private readonly StockAdjustmentModel $model,
        private readonly StockAdjustmentLineModel $lineModel,
    ) {}

    public function findById(int $id): ?StockAdjustment
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('adjustment_date')
            ->get()
            ->map(fn (StockAdjustmentModel $m) => $this->toEntity($m));
    }

    public function findByWarehouse(int $warehouseId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('adjustment_date')
            ->get()
            ->map(fn (StockAdjustmentModel $m) => $this->toEntity($m));
    }

    public function create(array $data): StockAdjustment
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?StockAdjustment
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function addLine(int $adjustmentId, array $lineData): StockAdjustmentLine
    {
        $lineData['adjustment_id'] = $adjustmentId;
        $record = $this->lineModel->newQueryWithoutScopes()->create($lineData);

        return $this->toLineEntity($record);
    }

    public function getLines(int $adjustmentId): Collection
    {
        return $this->lineModel->newQueryWithoutScopes()
            ->where('adjustment_id', $adjustmentId)
            ->get()
            ->map(fn (StockAdjustmentLineModel $m) => $this->toLineEntity($m));
    }

    private function toEntity(StockAdjustmentModel $model): StockAdjustment
    {
        $postedAt = null;
        if ($model->posted_at !== null) {
            $postedAt = new \DateTimeImmutable($model->posted_at->toDateTimeString());
        }

        return new StockAdjustment(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            warehouseId: (int) $model->warehouse_id,
            locationId: $model->location_id !== null ? (int) $model->location_id : null,
            referenceNo: (string) $model->reference_no,
            adjustmentDate: new \DateTimeImmutable($model->adjustment_date->toDateTimeString()),
            reason: (string) $model->reason,
            notes: $model->notes !== null ? (string) $model->notes : null,
            status: (string) $model->status,
            createdBy: $model->created_by !== null ? (int) $model->created_by : null,
            postedBy: $model->posted_by !== null ? (int) $model->posted_by : null,
            postedAt: $postedAt,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }

    private function toLineEntity(StockAdjustmentLineModel $model): StockAdjustmentLine
    {
        $expiryDate = null;
        if ($model->expiry_date !== null) {
            $expiryDate = new \DateTimeImmutable($model->expiry_date->toDateTimeString());
        }

        return new StockAdjustmentLine(
            id: (int) $model->id,
            adjustmentId: (int) $model->adjustment_id,
            productId: (int) $model->product_id,
            productVariantId: $model->product_variant_id !== null ? (int) $model->product_variant_id : null,
            expectedQty: (float) $model->expected_qty,
            actualQty: (float) $model->actual_qty,
            variance: (float) $model->variance,
            costPerUnit: (float) $model->cost_per_unit,
            batchNumber: $model->batch_number !== null ? (string) $model->batch_number : null,
            lotNumber: $model->lot_number !== null ? (string) $model->lot_number : null,
            serialNumber: $model->serial_number !== null ? (string) $model->serial_number : null,
            expiryDate: $expiryDate,
            notes: $model->notes !== null ? (string) $model->notes : null,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
