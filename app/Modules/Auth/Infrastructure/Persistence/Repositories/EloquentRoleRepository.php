<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\Repositories\RoleRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Models\PermissionModel;
use Modules\Auth\Infrastructure\Persistence\Models\RoleModel;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private readonly RoleModel $model,
    ) {}

    public function findById(int $id): ?Role
    {
        $model = $this->model->with('permissions')->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(int $tenantId, string $slug): ?Role
    {
        $model = $this->model->where('tenant_id', $tenantId)->where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (RoleModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Role
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): Role
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

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $model = $this->model->findOrFail($roleId);
        $model->permissions()->sync($permissionIds);
    }

    public function getPermissionIds(int $roleId): array
    {
        $model = $this->model->with('permissions')->find($roleId);

        return $model ? $model->permissions->pluck('id')->toArray() : [];
    }

    private function toEntity(RoleModel $model): Role
    {
        $permissions = $model->relationLoaded('permissions')
            ? $model->permissions->map(fn (PermissionModel $p) => $p->slug)->toArray()
            : [];

        return new Role(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            isSystem: (bool) $model->is_system,
            permissions: $permissions,
        );
    }
}
