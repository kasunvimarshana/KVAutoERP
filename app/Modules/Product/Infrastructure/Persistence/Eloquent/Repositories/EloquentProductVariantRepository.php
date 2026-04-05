<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;

final class EloquentProductVariantRepository implements ProductVariantRepositoryInterface
{
    public function __construct(
        private readonly ProductVariantModel $model,
    ) {}

    public function findById(int $id): ?ProductVariant
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findBySku(int $tenantId, string $sku): ?ProductVariant
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('sku', $sku)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByProduct(int $productId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('product_id', $productId)
            ->orderBy('name')
            ->get()
            ->map(fn (ProductVariantModel $m) => $this->toEntity($m));
    }

    public function create(array $data): ProductVariant
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?ProductVariant
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

    private function toEntity(ProductVariantModel $model): ProductVariant
    {
        return new ProductVariant(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            productId: (int) $model->product_id,
            sku: (string) $model->sku,
            barcode: $model->barcode !== null ? (string) $model->barcode : null,
            name: (string) $model->name,
            attributes: $model->attributes,
            costPrice: (float) $model->cost_price,
            sellingPrice: (float) $model->selling_price,
            weight: $model->weight !== null ? (float) $model->weight : null,
            isActive: (bool) $model->is_active,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
