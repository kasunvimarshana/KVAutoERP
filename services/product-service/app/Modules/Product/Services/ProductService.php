<?php

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    /**
     * List products with filtering, searching, sorting, and pagination.
     */
    public function listProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->findAll($filters, $perPage);
    }

    /**
     * Get a single product by ID.
     */
    public function getProduct(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Product with ID {$id} not found."
            );
        }

        return $product;
    }

    /**
     * Create a new product within an ACID transaction.
     */
    public function createProduct(ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            // Check for duplicate SKU
            if ($this->productRepository->findBySku($dto->sku)) {
                throw new \InvalidArgumentException(
                    "A product with SKU '{$dto->sku}' already exists."
                );
            }

            $product = $this->productRepository->create($dto);

            Log::info('Product created', ['product_id' => $product->id, 'sku' => $product->sku]);

            // Fire domain event
            Event::dispatch(new ProductCreated($product));

            return $product;
        });
    }

    /**
     * Update an existing product within an ACID transaction.
     */
    public function updateProduct(int $id, ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($id, $dto) {
            $existing = $this->productRepository->findById($id);

            if (!$existing) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    "Product with ID {$id} not found."
                );
            }

            // Check SKU uniqueness (excluding current product)
            $skuProduct = $this->productRepository->findBySku($dto->sku);
            if ($skuProduct && $skuProduct->id !== $id) {
                throw new \InvalidArgumentException(
                    "A product with SKU '{$dto->sku}' already exists."
                );
            }

            $changedAttributes = $this->getChangedAttributes($existing, $dto->toArray());
            $product           = $this->productRepository->update($id, $dto);

            Log::info('Product updated', ['product_id' => $product->id]);

            // Fire domain event
            Event::dispatch(new ProductUpdated($product, $changedAttributes));

            return $product;
        });
    }

    /**
     * Delete a product within an ACID transaction.
     */
    public function deleteProduct(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $product = $this->productRepository->findById($id);

            if (!$product) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    "Product with ID {$id} not found."
                );
            }

            $productId   = $product->id;
            $productSku  = $product->sku;
            $productName = $product->name;

            $result = $this->productRepository->delete($id);

            Log::info('Product deleted', ['product_id' => $productId]);

            // Fire domain event
            Event::dispatch(new ProductDeleted($productId, $productSku, $productName));

            return $result;
        });
    }

    /**
     * Determine which attributes changed for event tracking.
     */
    private function getChangedAttributes(Product $existing, array $newData): array
    {
        $changed = [];
        foreach ($newData as $key => $value) {
            if ($existing->{$key} != $value) {
                $changed[$key] = [
                    'from' => $existing->{$key},
                    'to'   => $value,
                ];
            }
        }
        return $changed;
    }
}
