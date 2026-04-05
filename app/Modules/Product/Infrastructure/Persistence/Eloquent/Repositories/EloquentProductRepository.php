<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly ProductModel $model,
    ) {}

    public function findById(int $id): ?Product
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findBySku(int $tenantId, string $sku): ?Product
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('sku', $sku)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByBarcode(int $tenantId, string $barcode): ?Product
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('barcode', $barcode)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCategory(int $tenantId, int $categoryId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->get()
            ->map(fn (ProductModel $m) => $this->toEntity($m));
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (ProductModel $m) => $this->toEntity($m));
    }

    public function findByType(int $tenantId, string $type): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->orderBy('name')
            ->get()
            ->map(fn (ProductModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Product
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Product
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    public function search(string $query, int $tenantId): Collection
    {
        $term = '%' . $query . '%';

        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                  ->orWhere('sku', 'like', $term);
            })
            ->orderBy('name')
            ->get()
            ->map(fn (ProductModel $m) => $this->toEntity($m));
    }

    private function toEntity(ProductModel $model): Product
    {
        return new Product(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            categoryId: $model->category_id !== null ? (int) $model->category_id : null,
            name: (string) $model->name,
            sku: (string) $model->sku,
            barcode: $model->barcode !== null ? (string) $model->barcode : null,
            type: (string) $model->type,
            status: (string) $model->status,
            description: $model->description !== null ? (string) $model->description : null,
            shortDescription: $model->short_description !== null ? (string) $model->short_description : null,
            unitOfMeasure: (string) $model->unit_of_measure,
            weight: $model->weight !== null ? (float) $model->weight : null,
            dimensions: $model->dimensions,
            images: $model->images,
            attributes: $model->attributes,
            taxClass: $model->tax_class !== null ? (string) $model->tax_class : null,
            costPrice: (float) $model->cost_price,
            sellingPrice: (float) $model->selling_price,
            isSerialized: (bool) $model->is_serialized,
            trackInventory: (bool) $model->track_inventory,
            minStockLevel: (float) $model->min_stock_level,
            maxStockLevel: $model->max_stock_level !== null ? (float) $model->max_stock_level : null,
            reorderPoint: (float) $model->reorder_point,
            leadTimeDays: (int) $model->lead_time_days,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
