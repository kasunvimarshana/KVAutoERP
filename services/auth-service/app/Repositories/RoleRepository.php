<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function findById(string $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    public function findByName(string $name, string $tenantId): ?Role
    {
        return Role::forTenant($tenantId)->where('name', $name)->first();
    }

    public function findByTenant(string $tenantId): Collection
    {
        return Role::forTenant($tenantId)->active()->with('permissions')->get();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(string $id, array $data): Role
    {
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role->fresh();
    }

    public function delete(string $id): bool
    {
        $role = Role::findOrFail($id);
        if ($role->is_system) {
            throw new \RuntimeException('System roles cannot be deleted.');
        }
        return (bool) $role->delete();
    }

    public function assignToUser(string $roleId, string $userId): void
    {
        Role::findOrFail($roleId)->users()->syncWithoutDetaching([$userId]);
    }

    public function revokeFromUser(string $roleId, string $userId): void
    {
        Role::findOrFail($roleId)->users()->detach($userId);
    }

    public function getUserRoles(string $userId): Collection
    {
        return Role::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->with('permissions')
            ->get();
    }

    public function syncUserRoles(string $userId, array $roleIds): void
    {
        \App\Models\User::findOrFail($userId)->roles()->sync($roleIds);
    }
}
