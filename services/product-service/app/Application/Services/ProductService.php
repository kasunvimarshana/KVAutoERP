<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Contracts\Repositories\ProductRepositoryInterface;
use App\Application\Contracts\Services\ProductServiceInterface;
use App\Application\DTOs\ProductDTO;
use App\Domain\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function getAllProducts(array $params): LengthAwarePaginator
    {
        $tenantId = $params['tenant_id'] ?? null;
        $conditions = $tenantId ? ['tenant_id' => $tenantId] : [];

        return $this->productRepository->paginate(
            params: $params,
            additionalConditions: $conditions,
        );
    }

    public function getProduct(int $id, string|int $tenantId): Product
    {
        return $this->productRepository->findById(
            id: $id,
            relations: ['category', 'variants'],
            fail: true
        );
    }

    public function createProduct(ProductDTO $dto): Product
    {
        $slug = Str::slug($dto->name) . '-' . Str::random(6);

        $product = $this->productRepository->create([
            'tenant_id' => $dto->tenantId,
            'category_id' => $dto->categoryId,
            'name' => $dto->name,
            'code' => strtoupper($dto->code),
            'slug' => $slug,
            'description' => $dto->description,
            'short_description' => $dto->shortDescription,
            'price' => $dto->price,
            'cost_price' => $dto->costPrice,
            'compare_price' => $dto->comparePrice,
            'sku' => $dto->sku,
            'barcode' => $dto->barcode,
            'unit' => $dto->unit,
            'weight' => $dto->weight,
            'dimensions' => $dto->dimensions,
            'images' => $dto->images,
            'attributes' => $dto->attributes,
            'tags' => $dto->tags,
            'is_active' => $dto->isActive,
            'is_featured' => $dto->isFeatured,
            'metadata' => $dto->metadata,
        ]);

        Log::info('Product created', ['product_id' => $product->id, 'tenant_id' => $dto->tenantId]);

        return $product->load(['category', 'variants']);
    }

    public function updateProduct(int $id, array $data, string|int $tenantId): Product
    {
        $this->productRepository->findById($id, fail: true);

        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $updated = $this->productRepository->update($id, $data);

        Log::info('Product updated', ['product_id' => $id, 'tenant_id' => $tenantId]);

        return $updated->load(['category', 'variants']);
    }

    public function deleteProduct(int $id, string|int $tenantId): bool
    {
        $result = $this->productRepository->delete($id);
        Log::info('Product deleted', ['product_id' => $id, 'tenant_id' => $tenantId]);
        return $result;
    }

    public function searchProducts(string $query, string|int $tenantId, array $params = []): LengthAwarePaginator
    {
        $params['search'] = $query;
        return $this->productRepository->paginate(
            params: $params,
            additionalConditions: ['tenant_id' => $tenantId],
        );
    }

    public function getProductsByCategory(int $categoryId, array $params = []): LengthAwarePaginator
    {
        return $this->productRepository->findByCategory($categoryId, $params);
    }
}
