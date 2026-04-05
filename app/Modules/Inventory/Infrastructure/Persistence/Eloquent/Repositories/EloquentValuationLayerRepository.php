<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\ValuationLayerModel;

final class EloquentValuationLayerRepository implements ValuationLayerRepositoryInterface
{
    public function __construct(
        private readonly ValuationLayerModel $model,
    ) {}

    public function findById(int $id): ?ValuationLayer
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByProduct(int $tenantId, int $productId, ?int $variantId = null): Collection
    {
        $query = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId);

        if ($variantId !== null) {
            $query->where('product_variant_id', $variantId);
        }

        return $query->orderBy('received_at')
            ->get()
            ->map(fn (ValuationLayerModel $m) => $this->toEntity($m));
    }

    public function getActiveByMethod(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        string $method,
    ): Collection {
        $query = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('is_exhausted', false)
            ->where('valuation_method', $method)
            ->when(
                $variantId !== null,
                fn ($q) => $q->where('product_variant_id', $variantId),
                fn ($q) => $q->whereNull('product_variant_id'),
            );

        $orderDirection = match (strtolower($method)) {
            'lifo'  => 'desc',
            default => 'asc',
        };

        return $query->orderBy('received_at', $orderDirection)
            ->get()
            ->map(fn (ValuationLayerModel $m) => $this->toEntity($m));
    }

    public function addLayer(array $data): ValuationLayer
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function consumeLayer(int $id, float $quantity): ValuationLayer
    {
        $record = $this->model->newQueryWithoutScopes()->findOrFail($id);

        $newRemaining = max(0.0, (float) $record->quantity_remaining - $quantity);

        $record->update([
            'quantity_remaining' => $newRemaining,
            'is_exhausted'       => $newRemaining <= 0.0,
        ]);

        return $this->toEntity($record->fresh());
    }

    public function update(int $id, array $data): ?ValuationLayer
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    private function toEntity(ValuationLayerModel $model): ValuationLayer
    {
        $expiryDate = null;
        if ($model->expiry_date !== null) {
            $expiryDate = new \DateTimeImmutable($model->expiry_date->toDateTimeString());
        }

        return new ValuationLayer(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            productId: (int) $model->product_id,
            productVariantId: $model->product_variant_id !== null ? (int) $model->product_variant_id : null,
            warehouseId: (int) $model->warehouse_id,
            batchNumber: $model->batch_number !== null ? (string) $model->batch_number : null,
            lotNumber: $model->lot_number !== null ? (string) $model->lot_number : null,
            serialNumber: $model->serial_number !== null ? (string) $model->serial_number : null,
            receivedAt: new \DateTimeImmutable($model->received_at->toDateTimeString()),
            expiryDate: $expiryDate,
            quantityReceived: (float) $model->quantity_received,
            quantityRemaining: (float) $model->quantity_remaining,
            costPerUnit: (float) $model->cost_per_unit,
            valuationMethod: (string) $model->valuation_method,
            isExhausted: (bool) $model->is_exhausted,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
