<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;

final class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function __construct(
        private readonly StockMovementModel $model,
    ) {}

    public function findById(int $id): ?StockMovement
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByProduct(int $tenantId, int $productId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->orderByDesc('moved_at')
            ->get()
            ->map(fn (StockMovementModel $m) => $this->toEntity($m));
    }

    public function findByType(int $tenantId, string $type): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->orderByDesc('moved_at')
            ->get()
            ->map(fn (StockMovementModel $m) => $this->toEntity($m));
    }

    public function findByDateRange(int $tenantId, string $from, string $to): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('moved_at', [$from, $to])
            ->orderByDesc('moved_at')
            ->get()
            ->map(fn (StockMovementModel $m) => $this->toEntity($m));
    }

    public function record(array $data): StockMovement
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    private function toEntity(StockMovementModel $model): StockMovement
    {
        $expiryDate = null;
        if ($model->expiry_date !== null) {
            $expiryDate = new \DateTimeImmutable($model->expiry_date->toDateTimeString());
        }

        return new StockMovement(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            productId: (int) $model->product_id,
            productVariantId: $model->product_variant_id !== null ? (int) $model->product_variant_id : null,
            fromLocationId: $model->from_location_id !== null ? (int) $model->from_location_id : null,
            toLocationId: $model->to_location_id !== null ? (int) $model->to_location_id : null,
            quantity: (float) $model->quantity,
            type: (string) $model->type,
            referenceType: $model->reference_type !== null ? (string) $model->reference_type : null,
            referenceId: $model->reference_id !== null ? (int) $model->reference_id : null,
            batchNumber: $model->batch_number !== null ? (string) $model->batch_number : null,
            lotNumber: $model->lot_number !== null ? (string) $model->lot_number : null,
            serialNumber: $model->serial_number !== null ? (string) $model->serial_number : null,
            expiryDate: $expiryDate,
            costPerUnit: (float) $model->cost_per_unit,
            notes: $model->notes !== null ? (string) $model->notes : null,
            movedBy: $model->moved_by !== null ? (int) $model->moved_by : null,
            movedAt: new \DateTimeImmutable($model->moved_at->toDateTimeString()),
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
