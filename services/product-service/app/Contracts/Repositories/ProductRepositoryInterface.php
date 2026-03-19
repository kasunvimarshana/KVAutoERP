<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Contract for the product repository.
 *
 * All methods operate within the current tenant scope (enforced by
 * the TenantAwareModel global scope).
 */
interface ProductRepositoryInterface
{
    /**
     * Return a paginated list of products, optionally filtered and sorted.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<Product>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator;

    /**
     * Find a single product by its UUID.
     *
     * @param  string  $id
     * @return Product|null
     */
    public function findById(string $id): ?Product;

    /**
     * Find a product by SKU within the current tenant.
     *
     * @param  string  $sku
     * @return Product|null
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Create a new product record.
     *
     * @param  array<string, mixed>  $data
     * @return Product
     */
    public function create(array $data): Product;

    /**
     * Update an existing product.
     *
     * @param  Product               $product
     * @param  array<string, mixed>  $data
     * @return Product
     */
    public function update(Product $product, array $data): Product;

    /**
     * Soft-delete a product.
     *
     * @param  Product  $product
     * @return void
     */
    public function delete(Product $product): void;
}
