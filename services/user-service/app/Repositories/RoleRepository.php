<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent-backed role repository.
 */
final class RoleRepository implements RoleRepositoryInterface
{
    /**
     * Find a role by its UUID (tenant-scoped).
     *
     * @param  string  $id
     * @return Role|null
     */
    public function findById(string $id): ?Role
    {
        return Role::with(['permissions'])->find($id);
    }

    /**
     * Find a role by its slug within a tenant (bypasses global scope).
     *
     * @param  string  $slug
     * @param  string  $tenantId
     * @return Role|null
     */
    public function findBySlugAndTenant(string $slug, string $tenantId): ?Role
    {
        return Role::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->with(['permissions'])
            ->first();
    }

    /**
     * Create a new role.
     *
     * @param  array<string, mixed>  $data
     * @return Role
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Update an existing role by UUID.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return Role
     */
    public function update(string $id, array $data): Role
    {
        $role = Role::findOrFail($id);
        $role->update($data);

        return $role->fresh(['permissions']) ?? $role;
    }

    /**
     * Delete a role by UUID.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $role = Role::find($id);

        if ($role === null) {
            return false;
        }

        return (bool) $role->delete();
    }

    /**
     * Return a paginated list of roles for a specific tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<Role>
     */
    public function paginateByTenant(string $tenantId, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return Role::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['permissions'])
            ->orderBy('hierarchy_level')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Attach a permission to a role (idempotent — uses syncWithoutDetaching).
     *
     * @param  string  $roleId
     * @param  string  $permissionId
     * @return void
     */
    public function assignPermission(string $roleId, string $permissionId): void
    {
        $role = Role::findOrFail($roleId);

        $role->permissions()->syncWithoutDetaching([$permissionId]);
    }

    /**
     * Detach a permission from a role.
     *
     * @param  string  $roleId
     * @param  string  $permissionId
     * @return void
     */
    public function revokePermission(string $roleId, string $permissionId): void
    {
        $role = Role::findOrFail($roleId);

        $role->permissions()->detach($permissionId);
    }

    /**
     * Return the permission slugs for a role.
     *
     * @param  string  $roleId
     * @return array<int, string>
     */
    public function getPermissionSlugs(string $roleId): array
    {
        $role = Role::with(['permissions'])->find($roleId);

        if ($role === null) {
            return [];
        }

        return $role->permissions->pluck('slug')->all();
    }
}
