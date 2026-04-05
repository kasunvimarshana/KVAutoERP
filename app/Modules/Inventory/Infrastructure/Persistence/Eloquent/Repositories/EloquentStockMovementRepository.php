<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;

class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function __construct(
        private readonly StockMovementModel $model,
    ) {}

    public function findById(int $id): ?StockMovement
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByProduct(int $tenantId, int $productId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->orderByDesc('performed_at')
            ->get()
            ->map(fn (StockMovementModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByWarehouse(int $tenantId, int $warehouseId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('performed_at')
            ->get()
            ->map(fn (StockMovementModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByReference(string $referenceType, int $referenceId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get()
            ->map(fn (StockMovementModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): StockMovement
    {
        if (! isset($data['created_at'])) {
            $data['created_at'] = now();
        }

        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    private function toEntity(StockMovementModel $model): StockMovement
    {
        return new StockMovement(
            id: $model->id,
            tenantId: $model->tenant_id,
            productId: $model->product_id,
            variantId: $model->variant_id,
            warehouseId: $model->warehouse_id,
            locationId: $model->location_id,
            type: $model->type,
            referenceType: $model->reference_type,
            referenceId: $model->reference_id,
            quantity: (float) $model->quantity,
            direction: $model->direction,
            unitCost: $model->unit_cost !== null ? (float) $model->unit_cost : null,
            batchId: $model->batch_id,
            lotNumber: $model->lot_number,
            serialNumber: $model->serial_number,
            notes: $model->notes,
            performedBy: $model->performed_by,
            performedAt: $model->performed_at,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
