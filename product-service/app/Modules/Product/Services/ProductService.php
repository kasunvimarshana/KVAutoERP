<?php

namespace App\Modules\Product\Services;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Services\Contracts\ProductServiceInterface;
use App\Services\InventoryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private InventoryService $inventoryService,
    ) {}

    public function getAllProducts(array $filters = []): LengthAwarePaginator
    {
        return $this->productRepository->getAll($filters);
    }

    public function getProduct(int $id): array
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new \RuntimeException("Product not found.", 404);
        }

        $inventory = $this->inventoryService->getInventoryByProductName($product->name);

        return [
            'product' => $product,
            'inventory' => $inventory,
        ];
    }

    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $product = $this->productRepository->create($data);

            event(new ProductCreated($product));

            return $product;
        });
    }

    public function updateProduct(int $id, array $data): Product
    {
        return DB::transaction(function () use ($id, $data): Product {
            $product = $this->productRepository->update($id, $data);

            event(new ProductUpdated($product));

            return $product;
        });
    }

    public function deleteProduct(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $product = $this->productRepository->findById($id);

            if (!$product) {
                throw new \RuntimeException("Product not found.", 404);
            }

            $deleted = $this->inventoryService->deleteByProductName($product->name);

            if (!$deleted) {
                throw new \RuntimeException("Failed to delete inventory records for product '{$product->name}'.", 502);
            }

            $result = $this->productRepository->delete($id);

            event(new ProductDeleted($product));

            return $result;
        });
    }
}
