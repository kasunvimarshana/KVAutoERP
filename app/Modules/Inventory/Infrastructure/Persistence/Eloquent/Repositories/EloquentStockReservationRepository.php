<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\StockReservation;
use Modules\Inventory\Domain\RepositoryInterfaces\StockReservationRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockReservationModel;

class EloquentStockReservationRepository implements StockReservationRepositoryInterface
{
    public function __construct(
        private readonly StockReservationModel $model,
    ) {}

    public function findById(int $id): ?StockReservation
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByReference(string $referenceType, int $referenceId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get()
            ->map(fn (StockReservationModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByProduct(int $tenantId, int $productId, ?int $variantId): array
    {
        $query = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId);

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        }

        return $query->get()
            ->map(fn (StockReservationModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): StockReservation
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?StockReservation
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function cancel(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        $record->fill(['status' => 'cancelled'])->save();

        return true;
    }

    private function toEntity(StockReservationModel $model): StockReservation
    {
        return new StockReservation(
            id: $model->id,
            tenantId: $model->tenant_id,
            productId: $model->product_id,
            variantId: $model->variant_id,
            warehouseId: $model->warehouse_id,
            locationId: $model->location_id,
            quantity: (float) $model->quantity,
            referenceType: $model->reference_type,
            referenceId: $model->reference_id,
            status: $model->status,
            expiresAt: $model->expires_at,
            notes: $model->notes,
            createdAt: $model->created_at,
        );
    }
}
