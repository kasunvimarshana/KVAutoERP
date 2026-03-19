<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Contract for the category repository.
 */
interface CategoryRepositoryInterface
{
    /**
     * Return a paginated list of categories.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<ProductCategory>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator;

    /**
     * Return all categories as a flat collection (no pagination).
     *
     * @return Collection<int, ProductCategory>
     */
    public function all(): Collection;

    /**
     * Find a category by its UUID.
     *
     * @param  string  $id
     * @return ProductCategory|null
     */
    public function findById(string $id): ?ProductCategory;

    /**
     * Create a new category.
     *
     * @param  array<string, mixed>  $data
     * @return ProductCategory
     */
    public function create(array $data): ProductCategory;

    /**
     * Update an existing category.
     *
     * @param  ProductCategory       $category
     * @param  array<string, mixed>  $data
     * @return ProductCategory
     */
    public function update(ProductCategory $category, array $data): ProductCategory;

    /**
     * Delete a category.
     *
     * @param  ProductCategory  $category
     * @return void
     */
    public function delete(ProductCategory $category): void;
}
