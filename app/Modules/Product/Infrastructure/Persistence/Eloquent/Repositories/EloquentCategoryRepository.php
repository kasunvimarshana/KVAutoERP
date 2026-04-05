<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\Category;
use Modules\Product\Domain\RepositoryInterfaces\CategoryRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\CategoryModel;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
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

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('path')
            ->get()
            ->map(fn (CategoryModel $m) => $this->toEntity($m));
    }

    public function getTree(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('path')
            ->get()
            ->map(fn (CategoryModel $m) => $this->toEntity($m));
    }

    public function getDescendants(int $categoryId): Collection
    {
        $parent = $this->model->newQueryWithoutScopes()->find($categoryId);

        if ($parent === null) {
            return collect();
        }

        $pathPrefix = $parent->path . $parent->id . '/';

        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $parent->tenant_id)
            ->where('path', 'like', $pathPrefix . '%')
            ->orderBy('path')
            ->get()
            ->map(fn (CategoryModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Category
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Category
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

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

    private function toEntity(CategoryModel $model): Category
    {
        return new Category(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            parentId: $model->parent_id !== null ? (int) $model->parent_id : null,
            name: (string) $model->name,
            slug: (string) $model->slug,
            description: $model->description !== null ? (string) $model->description : null,
            image: $model->image !== null ? (string) $model->image : null,
            isActive: (bool) $model->is_active,
            path: (string) $model->path,
            level: (int) $model->level,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
