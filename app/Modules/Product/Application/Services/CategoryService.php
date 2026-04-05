<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Product\Application\Contracts\CategoryServiceInterface;
use Modules\Product\Domain\Entities\Category;
use Modules\Product\Domain\RepositoryInterfaces\CategoryRepositoryInterface;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repo,
    ) {}

    public function createCategory(array $data): Category
    {
        $tenantId = (int) $data['tenant_id'];
        $code     = (string) $data['code'];

        if ($this->repo->findByCode($code, $tenantId) !== null) {
            throw new \InvalidArgumentException("Category code '{$code}' already exists for this tenant.");
        }

        $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
        $path     = $code;
        $level    = 0;

        if ($parentId !== null) {
            $parent = $this->repo->findById($parentId, $tenantId);
            if ($parent === null) {
                throw new \InvalidArgumentException("Parent category with id {$parentId} not found.");
            }
            $path  = $parent->path . '/' . $code;
            $level = $parent->level + 1;
        }

        $data['path']  = $path;
        $data['level'] = $level;

        return $this->repo->create($data);
    }

    public function updateCategory(int $id, array $data): Category
    {
        $category = $this->repo->findById($id, (int) $data['tenant_id']);

        if ($category === null) {
            throw new \InvalidArgumentException("Category with id {$id} not found.");
        }

        if (isset($data['code']) && $data['code'] !== $category->code) {
            $existing = $this->repo->findByCode((string) $data['code'], $category->tenantId);
            if ($existing !== null && $existing->id !== $id) {
                throw new \InvalidArgumentException("Category code '{$data['code']}' already exists for this tenant.");
            }
        }

        if (isset($data['parent_id'])) {
            $newParentId = (int) $data['parent_id'];
            if ($newParentId === $id) {
                throw new \RuntimeException('A category cannot be its own parent.');
            }
            $descendants = $this->repo->getDescendants($id, $category->tenantId);
            foreach ($descendants as $desc) {
                if ($desc->id === $newParentId) {
                    throw new \RuntimeException('Circular reference detected: new parent is a descendant of this category.');
                }
            }
            $parent = $this->repo->findById($newParentId, $category->tenantId);
            if ($parent === null) {
                throw new \InvalidArgumentException("Parent category with id {$newParentId} not found.");
            }
            $code          = $data['code'] ?? $category->code;
            $data['path']  = $parent->path . '/' . $code;
            $data['level'] = $parent->level + 1;
        }

        return $this->repo->update($id, $data);
    }

    public function deleteCategory(int $id, int $tenantId): bool
    {
        $category = $this->repo->findById($id, $tenantId);

        if ($category === null) {
            throw new \InvalidArgumentException("Category with id {$id} not found.");
        }

        return $this->repo->delete($id, $tenantId);
    }

    public function getCategory(int $id, int $tenantId): Category
    {
        $category = $this->repo->findById($id, $tenantId);

        if ($category === null) {
            throw new \InvalidArgumentException("Category with id {$id} not found.");
        }

        return $category;
    }

    public function getAll(int $tenantId): array
    {
        return $this->repo->allByTenant($tenantId);
    }

    public function getTree(int $tenantId): array
    {
        return $this->repo->getTree($tenantId);
    }

    public function getDescendants(int $id, int $tenantId): array
    {
        $category = $this->repo->findById($id, $tenantId);

        if ($category === null) {
            throw new \InvalidArgumentException("Category with id {$id} not found.");
        }

        return $this->repo->getDescendants($id, $tenantId);
    }
}
