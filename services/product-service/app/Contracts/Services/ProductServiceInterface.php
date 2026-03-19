<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the product application service.
 */
interface ProductServiceInterface
{
    /**
     * Return a paginated list of products with optional filtering.
     *
     * Supported filter keys: search, type, status, category_id,
     * sort_by, sort_dir, per_page.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<Product>
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a product by its UUID or throw NotFoundException.
     *
     * @param  string  $id
     * @return Product
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function findOrFail(string $id): Product;

    /**
     * Create a new product.
     *
     * @param  array<string, mixed>  $data
     * @return Product
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     */
    public function create(array $data): Product;

    /**
     * Update a product.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return Product
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function update(string $id, array $data): Product;

    /**
     * Soft-delete a product.
     *
     * @param  string  $id
     * @return void
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function delete(string $id): void;

    /**
     * Add a price to a product.
     *
     * @param  string                $productId
     * @param  array<string, mixed>  $data
     * @return ProductPrice
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function addPrice(string $productId, array $data): ProductPrice;

    /**
     * Return all prices for a product.
     *
     * @param  string  $productId
     * @return array<int, ProductPrice>
     */
    public function getPrices(string $productId): array;

    /**
     * Add a variant to a product.
     *
     * @param  string                $productId
     * @param  array<string, mixed>  $data
     * @return ProductVariant
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     */
    public function addVariant(string $productId, array $data): ProductVariant;

    /**
     * Return all variants for a product.
     *
     * @param  string  $productId
     * @return array<int, ProductVariant>
     */
    public function getVariants(string $productId): array;
}
