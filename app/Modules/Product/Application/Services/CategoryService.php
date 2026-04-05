<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Product\Application\Contracts\CategoryServiceInterface;
use Modules\Product\Domain\Entities\Category;
use Modules\Product\Domain\RepositoryInterfaces\CategoryRepositoryInterface;

final class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function getById(int $id): Category
    {
        $category = $this->categoryRepository->findById($id);

        if ($category === null) {
            throw new NotFoundException('Category', $id);
        }

        return $category;
    }

    public function getByTenant(int $tenantId): Collection
    {
        return $this->categoryRepository->findByTenant($tenantId);
    }

    public function getTree(int $tenantId): Collection
    {
        return $this->categoryRepository->getTree($tenantId);
    }

    public function getDescendants(int $categoryId): Collection
    {
        return $this->categoryRepository->getDescendants($categoryId);
    }

    public function create(array $data): Category
    {
        if (!empty($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        } else {
            $data['slug'] = Str::slug($data['name']);
        }

        if (!empty($data['parent_id'])) {
            $parent = $this->categoryRepository->findById((int) $data['parent_id']);

            if ($parent === null) {
                throw new NotFoundException('Category', $data['parent_id']);
            }

            $data['path']  = $parent->path . $parent->id . '/';
            $data['level'] = $parent->level + 1;
        } else {
            $data['path']  = '/';
            $data['level'] = 0;
        }

        return $this->categoryRepository->create($data);
    }

    public function update(int $id, array $data): Category
    {
        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        if (isset($data['parent_id']) || array_key_exists('parent_id', $data)) {
            $parentId = $data['parent_id'] ?? null;

            if ($parentId !== null) {
                $parent = $this->categoryRepository->findById((int) $parentId);

                if ($parent === null) {
                    throw new NotFoundException('Category', $parentId);
                }

                $data['path']  = $parent->path . $parent->id . '/';
                $data['level'] = $parent->level + 1;
            } else {
                $data['path']  = '/';
                $data['level'] = 0;
            }
        }

        $category = $this->categoryRepository->update($id, $data);

        if ($category === null) {
            throw new NotFoundException('Category', $id);
        }

        return $category;
    }

    public function delete(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }
}
