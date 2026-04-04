<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Domain\Entities\Permission;
use Modules\Auth\Domain\Repositories\PermissionRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Models\PermissionModel;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private readonly PermissionModel $model,
    ) {}

    public function findById(int $id): ?Permission
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Permission
    {
        $model = $this->model->where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->orderBy('module')
            ->orderBy('action')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (PermissionModel $m) => $this->toEntity($m));
    }

    public function findByModule(string $module): array
    {
        return $this->model
            ->where('module', $module)
            ->get()
            ->map(fn (PermissionModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Permission
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): Permission
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        $model = $this->model->find($id);

        return $model ? (bool) $model->delete() : false;
    }

    private function toEntity(PermissionModel $model): Permission
    {
        return new Permission(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            module: $model->module,
            action: $model->action,
        );
    }
}
