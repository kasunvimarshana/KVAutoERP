<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Eloquent-backed category repository.
 */
final class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Return a paginated list of categories.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<ProductCategory>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator
    {
        $query = ProductCategory::with('parent')->orderBy('sort_order')->orderBy('name');

        if ($filter !== null) {
            if ($filter->search !== null && $filter->search !== '') {
                $search = $filter->search;
                $query->where(static function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if (isset($filter->filters['is_active'])) {
                $query->where('is_active', (bool) $filter->filters['is_active']);
            }

            if (isset($filter->filters['parent_id'])) {
                $query->where('parent_id', $filter->filters['parent_id']);
            }
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Return all categories (no pagination).
     *
     * @return Collection<int, ProductCategory>
     */
    public function all(): Collection
    {
        return ProductCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find a category by UUID.
     *
     * @param  string  $id
     * @return ProductCategory|null
     */
    public function findById(string $id): ?ProductCategory
    {
        return ProductCategory::with(['parent', 'children'])->find($id);
    }

    /**
     * Create a new category.
     *
     * @param  array<string, mixed>  $data
     * @return ProductCategory
     */
    public function create(array $data): ProductCategory
    {
        return ProductCategory::create($data);
    }

    /**
     * Update an existing category.
     *
     * @param  ProductCategory       $category
     * @param  array<string, mixed>  $data
     * @return ProductCategory
     */
    public function update(ProductCategory $category, array $data): ProductCategory
    {
        $category->update($data);

        return $category->fresh(['parent', 'children']) ?? $category;
    }

    /**
     * Delete a category.
     *
     * @param  ProductCategory  $category
     * @return void
     */
    public function delete(ProductCategory $category): void
    {
        $category->delete();
    }
}
