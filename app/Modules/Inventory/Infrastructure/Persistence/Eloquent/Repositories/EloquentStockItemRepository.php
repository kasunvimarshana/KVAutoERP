<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\StockItem;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockItemModel;

class EloquentStockItemRepository implements StockItemRepositoryInterface
{
    public function __construct(
        private readonly StockItemModel $model,
    ) {}

    public function findById(int $id): ?StockItem
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByProduct(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
    ): ?StockItem {
        $query = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        if ($locationId !== null) {
            $query->where('location_id', $locationId);
        } else {
            $query->whereNull('location_id');
        }

        $record = $query->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByWarehouse(int $tenantId, int $warehouseId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->map(fn (StockItemModel $m) => $this->toEntity($m))
            ->all();
    }

    public function updateQuantity(int $id, float $qty): ?StockItem
    {
        return $this->update($id, ['quantity' => $qty]);
    }

    public function updateReserved(int $id, float $qty): ?StockItem
    {
        return $this->update($id, ['reserved_quantity' => $qty]);
    }

    public function upsert(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $qty,
        float $cost,
    ): StockItem {
        $existing = $this->findByProduct($tenantId, $productId, $variantId, $warehouseId, $locationId);

        if ($existing !== null) {
            return $this->update($existing->getId(), [
                'quantity'  => $qty,
                'unit_cost' => $cost,
            ]) ?? $existing;
        }

        return $this->create([
            'tenant_id'         => $tenantId,
            'product_id'        => $productId,
            'variant_id'        => $variantId,
            'warehouse_id'      => $warehouseId,
            'location_id'       => $locationId,
            'quantity'          => $qty,
            'reserved_quantity' => 0,
            'unit_cost'         => $cost,
        ]);
    }

    public function create(array $data): StockItem
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?StockItem
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function all(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (StockItemModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(StockItemModel $model): StockItem
    {
        return new StockItem(
            id: $model->id,
            tenantId: $model->tenant_id,
            productId: $model->product_id,
            variantId: $model->variant_id,
            warehouseId: $model->warehouse_id,
            locationId: $model->location_id,
            quantity: (float) $model->quantity,
            reservedQuantity: (float) $model->reserved_quantity,
            unitCost: (float) $model->unit_cost,
            lastMovementAt: $model->last_movement_at,
            createdAt: $model->created_at,
        );
    }
}
