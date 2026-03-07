<?php

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->productRepository->all($filters, $perPage);
    }

    public function get(int $id): Product
    {
        return $this->productRepository->find($id);
    }

    public function create(ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $product = $this->productRepository->create([
                'name' => $dto->name,
                'description' => $dto->description,
                'sku' => $dto->sku,
                'price' => $dto->price,
                'category' => $dto->category,
                'tenant_id' => $dto->tenantId,
                'attributes' => $dto->attributes,
                'is_active' => $dto->isActive ?? true,
            ]);

            event(new ProductCreated($product));

            return $product;
        });
    }

    public function update(int $id, ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($id, $dto) {
            $data = array_filter([
                'name' => $dto->name,
                'description' => $dto->description,
                'sku' => $dto->sku,
                'price' => $dto->price,
                'category' => $dto->category,
                'attributes' => $dto->attributes,
                'is_active' => $dto->isActive,
            ], fn($v) => $v !== null);

            $product = $this->productRepository->update($id, $data);
            event(new ProductUpdated($product));
            return $product;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $product = $this->productRepository->find($id);
            $result = $this->productRepository->delete($id);
            event(new ProductDeleted($product));
            return $result;
        });
    }
}
