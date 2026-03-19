<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Services\ProductServiceInterface;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPrice;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;
use KvEnterprise\SharedKernel\Exceptions\DomainException;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;

/**
 * Product application service.
 *
 * Orchestrates product CRUD, variant management, pricing, and image
 * handling.  All monetary values are stored via BCMath string arithmetic
 * to 4 decimal places.
 */
final class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Return a paginated list of products with optional filtering.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<Product>
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $sorts = [];
        if (!empty($filters['sort_by'])) {
            $sorts[] = [
                'field'     => $filters['sort_by'],
                'direction' => strtolower($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
            ];
        }

        $filterDTO = new FilterDTO(
            filters: array_filter([
                'type'        => $filters['type'] ?? null,
                'status'      => $filters['status'] ?? null,
                'category_id' => $filters['category_id'] ?? null,
                'cost_method' => $filters['cost_method'] ?? null,
            ], static fn ($v) => $v !== null && $v !== ''),
            sorts:   $sorts,
            search:  $filters['search'] ?? null,
        );

        return $this->productRepository->paginate($page, $perPage, $filterDTO);
    }

    /**
     * Find a product by UUID or throw NotFoundException.
     *
     * @param  string  $id
     * @return Product
     *
     * @throws NotFoundException
     */
    public function findOrFail(string $id): Product
    {
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw NotFoundException::for('Product', $id);
        }

        return $product;
    }

    /**
     * Create a new product.
     *
     * Handles slug generation, image creation, and ensures SKU uniqueness
     * within the tenant scope.
     *
     * @param  array<string, mixed>  $data
     * @return Product
     *
     * @throws ValidationException
     */
    public function create(array $data): Product
    {
        $this->assertSkuUnique($data['sku'] ?? '');

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['status'] = $data['status'] ?? 'active';
        $data['cost_method'] = $data['cost_method'] ?? config('product_service.product.default_cost_method');

        $images = $data['images'] ?? [];
        unset($data['images']);

        return DB::transaction(function () use ($data, $images): Product {
            $product = $this->productRepository->create($data);
            $this->syncImages($product, $images);

            return $product;
        });
    }

    /**
     * Update an existing product.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return Product
     *
     * @throws NotFoundException
     */
    public function update(string $id, array $data): Product
    {
        $product = $this->findOrFail($id);

        // Regenerate slug if name is changing and no explicit slug provided.
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // If SKU is changing, assert uniqueness for the new value.
        if (isset($data['sku']) && $data['sku'] !== $product->sku) {
            $this->assertSkuUnique($data['sku']);
        }

        $images = $data['images'] ?? null;
        unset($data['images']);

        return DB::transaction(function () use ($product, $data, $images): Product {
            $updated = $this->productRepository->update($product, $data);

            if ($images !== null) {
                $this->syncImages($updated, $images);
            }

            return $updated;
        });
    }

    /**
     * Soft-delete a product.
     *
     * @param  string  $id
     * @return void
     *
     * @throws NotFoundException
     */
    public function delete(string $id): void
    {
        $product = $this->findOrFail($id);
        $this->productRepository->delete($product);
    }

    /**
     * Add a price entry to a product.
     *
     * Monetary amounts are stored as BCMath strings with 4 decimal places.
     *
     * @param  string                $productId
     * @param  array<string, mixed>  $data
     * @return ProductPrice
     *
     * @throws NotFoundException
     */
    public function addPrice(string $productId, array $data): ProductPrice
    {
        $product = $this->findOrFail($productId);

        // Normalise price to 4 decimal places using BCMath.
        $rawPrice = (string) ($data['price'] ?? '0');
        $data['price'] = bcadd($rawPrice, '0', 4);

        if (isset($data['tier_min_qty'])) {
            $data['tier_min_qty'] = bcadd((string) $data['tier_min_qty'], '0', 6);
        }

        $data['product_id'] = $product->id;

        return ProductPrice::create($data);
    }

    /**
     * Return all price records for a product.
     *
     * @param  string  $productId
     * @return array<int, ProductPrice>
     */
    public function getPrices(string $productId): array
    {
        $product = $this->findOrFail($productId);

        return $product->prices()->get()->all();
    }

    /**
     * Add a variant to a product.
     *
     * @param  string                $productId
     * @param  array<string, mixed>  $data
     * @return ProductVariant
     *
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function addVariant(string $productId, array $data): ProductVariant
    {
        $product = $this->findOrFail($productId);

        if (!in_array($product->type, ['variant', 'bundle', 'composite'], true)) {
            throw new ValidationException(
                ['type' => ['Only products of type variant, bundle, or composite can have variants.']],
            );
        }

        $data['product_id'] = $product->id;
        $data['is_active']  = $data['is_active'] ?? true;

        // Assert variant SKU uniqueness within tenant scope.
        $exists = ProductVariant::where('sku', $data['sku'])->exists();
        if ($exists) {
            throw new ValidationException(['sku' => ['Variant SKU already exists for this tenant.']]);
        }

        return ProductVariant::create($data);
    }

    /**
     * Return all variants for a product.
     *
     * @param  string  $productId
     * @return array<int, ProductVariant>
     */
    public function getVariants(string $productId): array
    {
        $product = $this->findOrFail($productId);

        return $product->variants()->get()->all();
    }

    /**
     * Assert that an SKU is unique within the current tenant scope.
     *
     * @param  string  $sku
     * @return void
     *
     * @throws ValidationException
     */
    private function assertSkuUnique(string $sku): void
    {
        if ($sku === '') {
            return;
        }

        $existing = $this->productRepository->findBySku($sku);

        if ($existing !== null) {
            throw new ValidationException(['sku' => ['The SKU already exists for this tenant.']]);
        }
    }

    /**
     * Synchronise product images from an array of image data.
     *
     * Deletes existing images and replaces them with the provided set,
     * marking the first item (or any explicitly flagged item) as primary.
     *
     * @param  Product               $product
     * @param  array<int, mixed>     $images
     * @return void
     */
    private function syncImages(Product $product, array $images): void
    {
        if (empty($images)) {
            return;
        }

        // Remove existing images before replacing.
        ProductImage::where('product_id', $product->id)->delete();

        foreach ($images as $index => $image) {
            ProductImage::create([
                'product_id' => $product->id,
                'url'        => $image['url'],
                'alt_text'   => $image['alt_text'] ?? null,
                'sort_order' => $image['sort_order'] ?? $index,
                'is_primary' => $image['is_primary'] ?? ($index === 0),
            ]);
        }
    }
}
