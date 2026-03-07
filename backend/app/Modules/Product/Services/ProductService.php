<?php

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function list(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->productRepository->paginate($tenantId, $perPage, $filters);
    }

    public function findById(string $id, string $tenantId): Product
    {
        $product = $this->productRepository->findById($id, $tenantId);

        if (!$product) {
            throw new \RuntimeException("Product not found: {$id}");
        }

        return $product;
    }

    public function create(ProductDTO $dto): Product
    {
        $data       = $dto->toArray();
        $data['id'] = Str::uuid()->toString();

        $existing = $this->productRepository->findBySku($dto->sku, $dto->tenantId);
        if ($existing) {
            throw new \RuntimeException("Product SKU already exists: {$dto->sku}");
        }

        $product = $this->productRepository->create($data);

        Event::dispatch(new ProductCreated($product));

        return $product;
    }

    public function update(string $id, string $tenantId, array $data): Product
    {
        $product = $this->findById($id, $tenantId);

        $updated = $this->productRepository->update($product, $data);

        Event::dispatch(new ProductUpdated($updated));

        return $updated;
    }

    public function delete(string $id, string $tenantId): bool
    {
        $product = $this->findById($id, $tenantId);

        Event::dispatch(new ProductDeleted($product));

        return $this->productRepository->delete($product);
    }

    public function restore(string $id): bool
    {
        return $this->productRepository->restore($id);
    }
}
