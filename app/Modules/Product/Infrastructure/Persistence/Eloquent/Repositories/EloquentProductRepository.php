<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;

class EloquentProductRepository implements ProductRepositoryInterface
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

    public function findByCategory(int $tenantId, int $categoryId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('category_id', $categoryId)
            ->get()
            ->map(fn (ProductModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByType(int $tenantId, string $type): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->get()
            ->map(fn (ProductModel $m) => $this->toEntity($m))
            ->all();
    }

    public function search(int $tenantId, string $query): array
    {
        $like = '%' . $query . '%';

        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('barcode', 'like', $like);
            })
            ->get()
            ->map(fn (ProductModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Product
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Product
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

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

    public function all(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (ProductModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(ProductModel $model): Product
    {
        return new Product(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            sku: $model->sku,
            barcode: $model->barcode,
            type: $model->type,
            categoryId: $model->category_id,
            description: $model->description,
            unit: $model->unit,
            costPrice: (float) $model->cost_price,
            sellingPrice: (float) $model->selling_price,
            taxGroupId: $model->tax_group_id,
            trackInventory: (bool) $model->track_inventory,
            isActive: (bool) $model->is_active,
            weight: $model->weight !== null ? (float) $model->weight : null,
            dimensions: $model->dimensions,
            images: $model->images ?? [],
            tags: $model->tags ?? [],
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
