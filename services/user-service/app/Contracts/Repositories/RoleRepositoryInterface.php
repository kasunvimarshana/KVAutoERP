<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the Role repository.
 */
interface RoleRepositoryInterface
{
    /**
     * Find a role by its UUID.
     *
     * @param  string  $id
     * @return Role|null
     */
    public function findById(string $id): ?Role;

    /**
     * Find a role by its slug within a tenant.
     *
     * @param  string  $slug
     * @param  string  $tenantId
     * @return Role|null
     */
    public function findBySlugAndTenant(string $slug, string $tenantId): ?Role;

    /**
     * Create a new role.
     *
     * @param  array<string, mixed>  $data
     * @return Role
     */
    public function create(array $data): Role;

    /**
     * Update an existing role by UUID.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return Role
     */
    public function update(string $id, array $data): Role;

    /**
     * Delete a role by UUID.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Return a paginated list of roles for a tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<Role>
     */
    public function paginateByTenant(string $tenantId, int $perPage = 20, int $page = 1): LengthAwarePaginator;

    /**
     * Attach a permission to a role (idempotent).
     *
     * @param  string  $roleId
     * @param  string  $permissionId
     * @return void
     */
    public function assignPermission(string $roleId, string $permissionId): void;

    /**
     * Detach a permission from a role.
     *
     * @param  string  $roleId
     * @param  string  $permissionId
     * @return void
     */
    public function revokePermission(string $roleId, string $permissionId): void;

    /**
     * Return the permission slugs for a role.
     *
     * @param  string  $roleId
     * @return array<int, string>
     */
    public function getPermissionSlugs(string $roleId): array;
}
