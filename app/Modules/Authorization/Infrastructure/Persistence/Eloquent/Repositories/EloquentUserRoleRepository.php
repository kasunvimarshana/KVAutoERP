<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Authorization\Domain\RepositoryInterfaces\UserRoleRepositoryInterface;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Models\UserRoleModel;

class EloquentUserRoleRepository implements UserRoleRepositoryInterface
{
    public function __construct(
        private readonly UserRoleModel $model,
        private readonly RoleModel $roleModel,
    ) {}

    public function assignRole(int $userId, int $roleId, int $tenantId): void
    {
        $this->model->newQuery()->firstOrCreate(
            ['user_id' => $userId, 'role_id' => $roleId, 'tenant_id' => $tenantId],
        );
    }

    public function revokeRole(int $userId, int $roleId, int $tenantId): void
    {
        $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('tenant_id', $tenantId)
            ->delete();
    }

    public function getUserRoles(int $userId, int $tenantId): array
    {
        $roleIds = $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->pluck('role_id')
            ->all();

        if (empty($roleIds)) {
            return [];
        }

        return $this->roleModel->newQuery()
            ->whereIn('id', $roleIds)
            ->get()
            ->map(fn ($m) => $this->toRoleEntity($m))
            ->all();
    }

    public function hasPermission(int $userId, int $tenantId, string $permissionSlug): bool
    {
        $roleIds = $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->pluck('role_id')
            ->all();

        if (empty($roleIds)) {
            return false;
        }

        $permissionId = DB::table('permissions')
            ->where('slug', $permissionSlug)
            ->value('id');

        if ($permissionId === null) {
            return false;
        }

        return DB::table('role_permissions')
            ->whereIn('role_id', $roleIds)
            ->where('permission_id', $permissionId)
            ->exists();
    }

    public function hasRole(int $userId, int $tenantId, string $roleSlug): bool
    {
        $roleId = $this->roleModel->newQuery()
            ->where('slug', $roleSlug)
            ->value('id');

        if ($roleId === null) {
            return false;
        }

        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->where('role_id', $roleId)
            ->exists();
    }

    private function toRoleEntity(RoleModel $model): Role
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
}
