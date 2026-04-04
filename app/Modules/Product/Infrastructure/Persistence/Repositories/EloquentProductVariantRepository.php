<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\Repositories\ProductVariantRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Models\ProductVariantModel;

class EloquentProductVariantRepository implements ProductVariantRepositoryInterface
{
    public function __construct(
        private readonly ProductVariantModel $model,
    ) {}

    public function findById(int $id): ?ProductVariant
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByProduct(int $productId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->where('product_id', $productId)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (ProductVariantModel $m) => $this->toEntity($m));
    }

    public function create(array $data): ProductVariant
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): ProductVariant
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        $model = $this->model->find($id);
        if (! $model) {
            return false;
        }

        return (bool) $model->delete();
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
            price: $model->price !== null ? (float) $model->price : null,
            cost: $model->cost !== null ? (float) $model->cost : null,
            weight: $model->weight !== null ? (float) $model->weight : null,
            isActive: (bool) $model->is_active,
            stockManagement: (bool) $model->stock_management,
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
        );
    }
}
