<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Product\Domain\Entities\Category;
use Modules\Product\Domain\RepositoryInterfaces\CategoryRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\CategoryModel;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private readonly CategoryModel $model,
    ) {}

    public function findById(int $id): ?Category
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findBySlug(int $tenantId, string $slug): ?Category
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function getTree(int $tenantId): array
    {
        $categories = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(fn (CategoryModel $m) => $this->toEntity($m))
            ->all();

        return $this->buildTree($categories, null);
    }

    public function create(array $data): Category
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Category
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    private function buildTree(array $categories, ?int $parentId): array
    {
        $nodes = [];

        foreach ($categories as $category) {
            if ($category->getParentId() === $parentId) {
                $nodes[] = [
                    'category' => $category,
                    'children' => $this->buildTree($categories, $category->getId()),
                ];
            }
        }

        return $nodes;
    }

    private function toEntity(CategoryModel $model): Category
    {
        return new Category(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            slug: $model->slug,
            parentId: $model->parent_id,
            path: $model->path ?? '/',
            level: (int) $model->level,
            description: $model->description,
            isActive: (bool) $model->is_active,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
