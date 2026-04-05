<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;

class EloquentProductVariantRepository implements ProductVariantRepositoryInterface
{
    public function __construct(
        private readonly ProductVariantModel $model,
    ) {}

    public function findById(int $id): ?ProductVariant
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByProduct(int $productId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('product_id', $productId)
            ->get()
            ->map(fn (ProductVariantModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findBySku(int $tenantId, string $sku): ?ProductVariant
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('sku', $sku)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function create(array $data): ProductVariant
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?ProductVariant
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

    private function toEntity(ProductVariantModel $model): ProductVariant
    {
        return new ProductVariant(
            id: $model->id,
            tenantId: $model->tenant_id,
            productId: $model->product_id,
            name: $model->name,
            sku: $model->sku,
            barcode: $model->barcode,
            attributes: $model->attributes ?? [],
            costPrice: $model->cost_price !== null ? (float) $model->cost_price : null,
            sellingPrice: $model->selling_price !== null ? (float) $model->selling_price : null,
            stockQty: (float) $model->stock_qty,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at,
        );
    }
}
