<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Models\ProductModel;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly ProductModel $model,
    ) {}

    public function findById(int $id): ?Product
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySku(string $sku): ?Product
    {
        $model = $this->model->where('sku', $sku)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByBarcode(string $barcode): ?Product
    {
        $model = $this->model->where('barcode', $barcode)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (ProductModel $m) => $this->toEntity($m));
    }

    public function findByCategory(int $categoryId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (ProductModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Product
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): Product
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

    private function toEntity(ProductModel $model): Product
    {
        return new Product(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            sku: $model->sku,
            barcode: $model->barcode,
            type: $model->type,
            status: $model->status,
            categoryId: $model->category_id,
            description: $model->description,
            shortDescription: $model->short_description,
            weight: $model->weight !== null ? (float) $model->weight : null,
            dimensions: $model->dimensions,
            images: $model->images,
            tags: $model->tags,
            isTaxable: (bool) $model->is_taxable,
            taxClass: $model->tax_class,
            hasSerial: (bool) $model->has_serial,
            hasBatch: (bool) $model->has_batch,
            hasLot: (bool) $model->has_lot,
            isSerialized: (bool) $model->is_serialized,
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
        );
    }
}
