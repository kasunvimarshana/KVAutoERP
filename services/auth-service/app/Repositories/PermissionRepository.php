<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function findById(string $id): ?Permission
    {
        return Permission::find($id);
    }

    public function findByName(string $name): ?Permission
    {
        return Permission::where('name', $name)->first();
    }

    public function findByTenant(string $tenantId): Collection
    {
        // Permissions can be global or tenant-specific via roles
        return Permission::all();
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(string $id, array $data): Permission
    {
        $permission = Permission::findOrFail($id);
        $permission->update($data);
        return $permission->fresh();
    }

    public function delete(string $id): bool
    {
        $permission = Permission::findOrFail($id);
        if ($permission->is_system) {
            throw new \RuntimeException('System permissions cannot be deleted.');
        }
        return (bool) $permission->delete();
    }

    public function assignToRole(string $permissionId, string $roleId): void
    {
        \App\Models\Role::findOrFail($roleId)->permissions()->syncWithoutDetaching([$permissionId]);
    }

    public function revokeFromRole(string $permissionId, string $roleId): void
    {
        \App\Models\Role::findOrFail($roleId)->permissions()->detach($permissionId);
    }

    public function assignDirectlyToUser(string $permissionId, string $userId): void
    {
        \App\Models\User::findOrFail($userId)->directPermissions()->syncWithoutDetaching([
            $permissionId => ['granted' => true],
        ]);
    }

    public function revokeDirectlyFromUser(string $permissionId, string $userId): void
    {
        \App\Models\User::findOrFail($userId)->directPermissions()->detach($permissionId);
    }

    public function getUserPermissions(string $userId): Collection
    {
        return Permission::whereHas('users', fn ($q) => $q->where('user_id', $userId)->where('granted', true))
            ->get();
    }

    public function syncRolePermissions(string $roleId, array $permissionIds): void
    {
        \App\Models\Role::findOrFail($roleId)->permissions()->sync($permissionIds);
    }
}
