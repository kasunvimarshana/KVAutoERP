<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the Role application service.
 */
interface RoleServiceInterface
{
    /**
     * Create a new role within a tenant.
     *
     * @param  array<string, mixed>  $data
     * @param  string                $tenantId
     * @param  string                $actorId
     * @return Role
     */
    public function createRole(array $data, string $tenantId, string $actorId): Role;

    /**
     * Update an existing role.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @param  string                $actorId
     * @return Role
     */
    public function updateRole(string $id, array $data, string $actorId): Role;

    /**
     * Delete a role by UUID.
     *
     * @param  string  $id
     * @return bool
     */
    public function deleteRole(string $id): bool;

    /**
     * Assign a permission to a role.
     *
     * @param  string  $roleId
     * @param  string  $permissionId
     * @return void
     */
    public function assignPermission(string $roleId, string $permissionId): void;

    /**
     * Revoke a permission from a role.
     *
     * @param  string  $roleId
     * @param  string  $permissionId
     * @return void
     */
    public function revokePermission(string $roleId, string $permissionId): void;

    /**
     * Return a paginated list of roles for a tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<Role>
     */
    public function listByTenant(string $tenantId, int $perPage, int $page): LengthAwarePaginator;

    /**
     * Find a role by UUID.
     *
     * @param  string  $id
     * @return Role|null
     */
    public function findById(string $id): ?Role;
}
