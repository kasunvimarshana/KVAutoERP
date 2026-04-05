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

    public function findById(int $id, int $tenantId): ?Category
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findByCode(string $code, int $tenantId): ?Category
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('code', $code)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (CategoryModel $m) => $this->toDomain($m))
            ->all();
    }

    public function getTree(int $tenantId): array
    {
        $all  = $this->allByTenant($tenantId);
        $map  = [];
        $tree = [];

        foreach ($all as $cat) {
            $map[$cat->id] = array_merge((array) $cat, ['children' => []]);
        }

        foreach ($map as $id => &$node) {
            if ($node['parentId'] !== null && isset($map[$node['parentId']])) {
                $map[$node['parentId']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }

        return $tree;
    }

    public function getDescendants(int $id, int $tenantId): array
    {
        $parent = $this->findById($id, $tenantId);

        if ($parent === null) {
            return [];
        }

        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('path', 'LIKE', $parent->path . '/%')
            ->get()
            ->map(fn (CategoryModel $m) => $this->toDomain($m))
            ->all();
    }

    public function create(array $data): Category
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toDomain($record);
    }

    public function update(int $id, array $data): Category
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->findOrFail($id);

        $record->update($data);

        return $this->toDomain($record->fresh());
    }

    public function delete(int $id, int $tenantId): bool
    {
        return (bool) $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
    }

    private function toDomain(CategoryModel $model): Category
    {
        return new Category(
            id:          $model->id,
            tenantId:    $model->tenant_id,
            name:        $model->name,
            code:        $model->code,
            parentId:    $model->parent_id,
            path:        $model->path,
            level:       $model->level,
            description: $model->description,
            isActive:    (bool) $model->is_active,
            createdAt:   $model->created_at
                ? \DateTimeImmutable::createFromInterface($model->created_at)
                : null,
            updatedAt:   $model->updated_at
                ? \DateTimeImmutable::createFromInterface($model->updated_at)
                : null,
        );
    }
}
