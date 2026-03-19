<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Services\CategoryServiceInterface;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;
use KvEnterprise\SharedKernel\Exceptions\DomainException;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;

/**
 * Category application service.
 */
final class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * Return a paginated list of categories.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<ProductCategory>
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $filterDTO = new FilterDTO(
            filters: array_filter([
                'is_active' => isset($filters['is_active']) ? (bool) $filters['is_active'] : null,
                'parent_id' => $filters['parent_id'] ?? null,
            ], static fn ($v) => $v !== null),
            search: $filters['search'] ?? null,
        );

        return $this->categoryRepository->paginate($page, $perPage, $filterDTO);
    }

    /**
     * Return all categories as a flat collection.
     *
     * @return Collection<int, ProductCategory>
     */
    public function all(): Collection
    {
        return $this->categoryRepository->all();
    }

    /**
     * Find a category or throw NotFoundException.
     *
     * @param  string  $id
     * @return ProductCategory
     *
     * @throws NotFoundException
     */
    public function findOrFail(string $id): ProductCategory
    {
        $category = $this->categoryRepository->findById($id);

        if ($category === null) {
            throw NotFoundException::for('ProductCategory', $id);
        }

        return $category;
    }

    /**
     * Create a new category.
     *
     * @param  array<string, mixed>  $data
     * @return ProductCategory
     */
    public function create(array $data): ProductCategory
    {
        $data['slug']      = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $this->categoryRepository->create($data);
    }

    /**
     * Update a category.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return ProductCategory
     *
     * @throws NotFoundException
     */
    public function update(string $id, array $data): ProductCategory
    {
        $category = $this->findOrFail($id);

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->categoryRepository->update($category, $data);
    }

    /**
     * Delete a category.
     *
     * Refuses deletion if the category has active products.
     *
     * @param  string  $id
     * @return void
     *
     * @throws NotFoundException
     * @throws DomainException
     */
    public function delete(string $id): void
    {
        $category = $this->findOrFail($id);

        if ($category->products()->exists()) {
            throw new DomainException(
                'Cannot delete a category that has products assigned to it.',
                ['category_id' => $id],
            );
        }

        if ($category->children()->exists()) {
            throw new DomainException(
                'Cannot delete a category that has child categories.',
                ['category_id' => $id],
            );
        }

        $this->categoryRepository->delete($category);
    }
}
