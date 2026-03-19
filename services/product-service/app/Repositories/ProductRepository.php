<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Eloquent-backed product repository.
 *
 * All queries are automatically tenant-scoped via TenantAwareModel's
 * global scope, so no explicit tenant_id filtering is required here.
 */
final class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Return a paginated list of products, optionally filtered and sorted.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<Product>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator
    {
        $query = Product::with(['category', 'baseUom', 'buyingUom', 'sellingUom']);

        if ($filter !== null) {
            // Full-text search across name, sku, barcode, description.
            if ($filter->search !== null && $filter->search !== '') {
                $search = $filter->search;
                $query->where(static function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Equality filters.
            foreach (['type', 'status', 'category_id', 'cost_method'] as $column) {
                if (isset($filter->filters[$column]) && $filter->filters[$column] !== '') {
                    $query->where($column, $filter->filters[$column]);
                }
            }

            // Sorting.
            foreach ($filter->sorts as $sort) {
                $query->orderBy($sort['field'], $sort['direction']);
            }
        }

        if ($query->getQuery()->orders === null) {
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find a single product by its UUID.
     *
     * @param  string  $id
     * @return Product|null
     */
    public function findById(string $id): ?Product
    {
        return Product::with(['category', 'baseUom', 'buyingUom', 'sellingUom', 'prices', 'variants', 'productImages'])
            ->find($id);
    }

    /**
     * Find a product by SKU within the current tenant scope.
     *
     * @param  string  $sku
     * @return Product|null
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * Create a new product record.
     *
     * @param  array<string, mixed>  $data
     * @return Product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update an existing product.
     *
     * @param  Product               $product
     * @param  array<string, mixed>  $data
     * @return Product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh(['category', 'baseUom', 'buyingUom', 'sellingUom']) ?? $product;
    }

    /**
     * Soft-delete a product.
     *
     * @param  Product  $product
     * @return void
     */
    public function delete(Product $product): void
    {
        $product->delete();
    }
}
