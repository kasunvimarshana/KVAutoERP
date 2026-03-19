<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent-backed permission repository.
 */
final class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * Find a permission by its UUID (tenant-scoped).
     *
     * @param  string  $id
     * @return Permission|null
     */
    public function findById(string $id): ?Permission
    {
        return Permission::find($id);
    }

    /**
     * Find a permission by slug within a tenant (bypasses global scope).
     *
     * @param  string  $slug
     * @param  string  $tenantId
     * @return Permission|null
     */
    public function findBySlugAndTenant(string $slug, string $tenantId): ?Permission
    {
        return Permission::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Create a new permission.
     *
     * @param  array<string, mixed>  $data
     * @return Permission
     */
    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    /**
     * Update an existing permission by UUID.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return Permission
     */
    public function update(string $id, array $data): Permission
    {
        $permission = Permission::findOrFail($id);
        $permission->update($data);

        return $permission->fresh() ?? $permission;
    }

    /**
     * Delete a permission by UUID.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $permission = Permission::find($id);

        if ($permission === null) {
            return false;
        }

        return (bool) $permission->delete();
    }

    /**
     * Return a paginated list of permissions for a specific tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<Permission>
     */
    public function paginateByTenant(string $tenantId, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return Permission::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('module')
            ->orderBy('action')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
