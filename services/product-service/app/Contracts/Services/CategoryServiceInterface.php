<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the category application service.
 */
interface CategoryServiceInterface
{
    /**
     * Return a paginated list of categories.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<ProductCategory>
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Return all categories as a flat collection (for select inputs).
     *
     * @return Collection<int, ProductCategory>
     */
    public function all(): Collection;

    /**
     * Find a category or throw NotFoundException.
     *
     * @param  string  $id
     * @return ProductCategory
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function findOrFail(string $id): ProductCategory;

    /**
     * Create a new category.
     *
     * @param  array<string, mixed>  $data
     * @return ProductCategory
     */
    public function create(array $data): ProductCategory;

    /**
     * Update a category.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return ProductCategory
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function update(string $id, array $data): ProductCategory;

    /**
     * Delete a category.
     *
     * @param  string  $id
     * @return void
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     * @throws \KvEnterprise\SharedKernel\Exceptions\DomainException
     */
    public function delete(string $id): void;
}
