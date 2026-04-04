<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Repositories;

use Modules\Auth\Domain\Entities\UserRole;
use Modules\Auth\Domain\Repositories\UserRoleRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Models\RoleModel;
use Modules\Auth\Infrastructure\Persistence\Models\UserRoleModel;

class EloquentUserRoleRepository implements UserRoleRepositoryInterface
{
    public function __construct(
        private readonly UserRoleModel $model,
        private readonly RoleModel $roleModel,
    ) {}

    public function assign(int $userId, int $roleId, int $tenantId): UserRole
    {
        $this->model->firstOrCreate([
            'user_id'   => $userId,
            'role_id'   => $roleId,
            'tenant_id' => $tenantId,
        ]);

        return new UserRole($userId, $roleId, $tenantId);
    }

    public function revoke(int $userId, int $roleId, int $tenantId): bool
    {
        return (bool) $this->model
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('tenant_id', $tenantId)
            ->delete();
    }

    public function getUserRoles(int $userId, int $tenantId): array
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->pluck('role_id')
            ->toArray();
    }

    public function getUserPermissions(int $userId, int $tenantId): array
    {
        $roleIds = $this->getUserRoles($userId, $tenantId);

        if (empty($roleIds)) {
            return [];
        }

        return $this->roleModel
            ->whereIn('id', $roleIds)
            ->with('permissions')
            ->get()
            ->flatMap(fn (RoleModel $role) => $role->permissions->pluck('slug'))
            ->unique()
            ->values()
            ->toArray();
    }

    public function hasRole(int $userId, int $roleId, int $tenantId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('tenant_id', $tenantId)
            ->exists();
    }
}
