<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\StockItem;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockItemModel;

final class EloquentStockItemRepository implements StockItemRepositoryInterface
{
    public function __construct(
        private readonly StockItemModel $model,
    ) {}

    public function findById(int $id): ?StockItem
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByProduct(int $tenantId, int $productId, ?int $variantId = null): Collection
    {
        $query = $this->model->newQueryWithoutScopes()
            ->where('product_id', $productId);

        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }

        if ($variantId !== null) {
            $query->where('product_variant_id', $variantId);
        }

        return $query->get()->map(fn (StockItemModel $m) => $this->toEntity($m));
    }

    public function findByLocation(int $locationId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('location_id', $locationId)
            ->get()
            ->map(fn (StockItemModel $m) => $this->toEntity($m));
    }

    public function findByWarehouse(int $warehouseId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->map(fn (StockItemModel $m) => $this->toEntity($m));
    }

    public function findPosition(int $productId, ?int $variantId, int $warehouseId, ?int $locationId): ?StockItem
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->when(
                $variantId !== null,
                fn ($q) => $q->where('product_variant_id', $variantId),
                fn ($q) => $q->whereNull('product_variant_id'),
            )
            ->when(
                $locationId !== null,
                fn ($q) => $q->where('location_id', $locationId),
                fn ($q) => $q->whereNull('location_id'),
            )
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function updateQuantity(int $id, array $data): ?StockItem
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function upsertPosition(array $data): StockItem
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('product_id', $data['product_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->when(
                isset($data['product_variant_id']) && $data['product_variant_id'] !== null,
                fn ($q) => $q->where('product_variant_id', $data['product_variant_id']),
                fn ($q) => $q->whereNull('product_variant_id'),
            )
            ->when(
                isset($data['location_id']) && $data['location_id'] !== null,
                fn ($q) => $q->where('location_id', $data['location_id']),
                fn ($q) => $q->whereNull('location_id'),
            )
            ->first();

        if ($record !== null) {
            $record->update($data);
        } else {
            $record = $this->model->newQueryWithoutScopes()->create($data);
        }

        return $this->toEntity($record->fresh());
    }

    public function reserve(int $id, float $quantity): bool
    {
        return (bool) $this->model->newQueryWithoutScopes()
            ->where('id', $id)
            ->update([
                'quantity_reserved'  => \Illuminate\Support\Facades\DB::raw("quantity_reserved + {$quantity}"),
                'quantity_available' => \Illuminate\Support\Facades\DB::raw("quantity_available - {$quantity}"),
            ]);
    }

    public function release(int $id, float $quantity): bool
    {
        return (bool) $this->model->newQueryWithoutScopes()
            ->where('id', $id)
            ->update([
                'quantity_reserved'  => \Illuminate\Support\Facades\DB::raw("quantity_reserved - {$quantity}"),
                'quantity_available' => \Illuminate\Support\Facades\DB::raw("quantity_available + {$quantity}"),
            ]);
    }

    private function toEntity(StockItemModel $model): StockItem
    {
        return new StockItem(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            productId: (int) $model->product_id,
            productVariantId: $model->product_variant_id !== null ? (int) $model->product_variant_id : null,
            warehouseId: (int) $model->warehouse_id,
            locationId: $model->location_id !== null ? (int) $model->location_id : null,
            quantityAvailable: (float) $model->quantity_available,
            quantityReserved: (float) $model->quantity_reserved,
            quantityOnOrder: (float) $model->quantity_on_order,
            unitOfMeasure: (string) $model->unit_of_measure,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
