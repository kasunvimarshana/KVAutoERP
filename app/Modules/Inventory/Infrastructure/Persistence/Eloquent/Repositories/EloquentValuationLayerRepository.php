<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\ValuationLayerModel;

class EloquentValuationLayerRepository implements ValuationLayerRepositoryInterface
{
    public function __construct(
        private readonly ValuationLayerModel $model,
    ) {}

    public function findByProduct(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
    ): array {
        $query = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        return $query->get()
            ->map(fn (ValuationLayerModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): ValuationLayer
    {
        if (! isset($data['created_at'])) {
            $data['created_at'] = now();
        }

        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?ValuationLayer
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function findAvailable(
        int $tenantId,
        int $productId,
        ?int $variantId,
        string $method,
    ): array {
        $query = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('quantity', '>', 0);

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        $direction = match (strtolower($method)) {
            'lifo'  => 'desc',
            default => 'asc',
        };

        $query->orderBy('created_at', $direction);

        return $query->get()
            ->map(fn (ValuationLayerModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(ValuationLayerModel $model): ValuationLayer
    {
        return new ValuationLayer(
            id: $model->id,
            tenantId: $model->tenant_id,
            productId: $model->product_id,
            variantId: $model->variant_id,
            warehouseId: $model->warehouse_id,
            locationId: $model->location_id,
            batchId: $model->batch_id,
            quantity: (float) $model->quantity,
            originalQuantity: (float) $model->original_quantity,
            unitCost: (float) $model->unit_cost,
            method: $model->method,
            createdAt: $model->created_at,
        );
    }
}
