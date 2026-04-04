<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Authorization\Domain\Entities\Permission;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Models\RoleModel;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private readonly RoleModel $model,
        private readonly PermissionModel $permissionModel,
    ) {}

    public function findById(int $id): ?Role
    {
        $model = $this->model->newQuery()->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Role
    {
        $model = $this->model->newQuery()->where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAllByTenant(int $tenantId): array
    {
        return $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn ($m) => $this->toEntity($m))
            ->all();
    }

    public function save(Role $role): Role
    {
        if ($role->id === null) {
            $model = $this->model->newQuery()->create([
                'tenant_id' => $role->tenantId,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'is_system' => $role->isSystem,
            ]);
        } else {
            $model = $this->model->newQuery()->findOrFail($role->id);
            $model->update([
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
            ]);
            $model->refresh();
        }

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        $this->model->newQuery()->findOrFail($id)->delete();
    }

    public function assignPermission(int $roleId, int $permissionId): void
    {
        \Illuminate\Support\Facades\DB::table('role_permissions')->insertOrIgnore([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function revokePermission(int $roleId, int $permissionId): void
    {
        \Illuminate\Support\Facades\DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();
    }

    public function getPermissions(int $roleId): array
    {
        $permissionIds = \Illuminate\Support\Facades\DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->pluck('permission_id')
            ->all();

        if (empty($permissionIds)) {
            return [];
        }

        return $this->permissionModel->newQuery()
            ->whereIn('id', $permissionIds)
            ->get()
            ->map(fn ($m) => $this->toPermissionEntity($m))
            ->all();
    }

    private function toEntity(RoleModel $model): Role
    {
        return new Role(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            isSystem: (bool) $model->is_system,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    private function toPermissionEntity(PermissionModel $model): Permission
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
