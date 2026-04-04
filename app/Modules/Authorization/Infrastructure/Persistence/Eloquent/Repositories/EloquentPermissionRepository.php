<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Authorization\Domain\Entities\Permission;
use Modules\Authorization\Domain\RepositoryInterfaces\PermissionRepositoryInterface;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Models\PermissionModel;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private readonly PermissionModel $model,
    ) {}

    public function findById(int $id): ?Permission
    {
        $model = $this->model->newQuery()->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Permission
    {
        $model = $this->model->newQuery()->where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(): array
    {
        return $this->model->newQuery()
            ->get()
            ->map(fn ($m) => $this->toEntity($m))
            ->all();
    }

    public function findByModule(string $module): array
    {
        return $this->model->newQuery()
            ->where('module', $module)
            ->get()
            ->map(fn ($m) => $this->toEntity($m))
            ->all();
    }

    public function save(Permission $permission): Permission
    {
        if ($permission->id === null) {
            $model = $this->model->newQuery()->create([
                'name' => $permission->name,
                'slug' => $permission->slug,
                'module' => $permission->module,
                'description' => $permission->description,
            ]);
        } else {
            $model = $this->model->newQuery()->findOrFail($permission->id);
            $model->update([
                'name' => $permission->name,
                'slug' => $permission->slug,
                'module' => $permission->module,
                'description' => $permission->description,
            ]);
            $model->refresh();
        }

        return $this->toEntity($model);
    }

    private function toEntity(PermissionModel $model): Permission
    {
        return new Permission(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            module: $model->module,
            description: $model->description,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
