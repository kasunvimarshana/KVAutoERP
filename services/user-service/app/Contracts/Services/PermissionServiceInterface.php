<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the Permission application service.
 */
interface PermissionServiceInterface
{
    /**
     * Create a new permission within a tenant.
     *
     * @param  array<string, mixed>  $data
     * @param  string                $tenantId
     * @param  string                $actorId
     * @return Permission
     */
    public function createPermission(array $data, string $tenantId, string $actorId): Permission;

    /**
     * Update an existing permission.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @param  string                $actorId
     * @return Permission
     */
    public function updatePermission(string $id, array $data, string $actorId): Permission;

    /**
     * Delete a permission by UUID.
     *
     * @param  string  $id
     * @return bool
     */
    public function deletePermission(string $id): bool;

    /**
     * Return a paginated list of permissions for a tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<Permission>
     */
    public function listByTenant(string $tenantId, int $perPage, int $page): LengthAwarePaginator;

    /**
     * Find a permission by UUID.
     *
     * @param  string  $id
     * @return Permission|null
     */
    public function findById(string $id): ?Permission;
}
