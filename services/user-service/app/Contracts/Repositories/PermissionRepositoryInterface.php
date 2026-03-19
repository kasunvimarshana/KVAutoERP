<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the Permission repository.
 */
interface PermissionRepositoryInterface
{
    /**
     * Find a permission by its UUID.
     *
     * @param  string  $id
     * @return Permission|null
     */
    public function findById(string $id): ?Permission;

    /**
     * Find a permission by its slug within a tenant.
     *
     * @param  string  $slug
     * @param  string  $tenantId
     * @return Permission|null
     */
    public function findBySlugAndTenant(string $slug, string $tenantId): ?Permission;

    /**
     * Create a new permission.
     *
     * @param  array<string, mixed>  $data
     * @return Permission
     */
    public function create(array $data): Permission;

    /**
     * Update an existing permission by UUID.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return Permission
     */
    public function update(string $id, array $data): Permission;

    /**
     * Delete a permission by UUID.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Return a paginated list of permissions for a tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<Permission>
     */
    public function paginateByTenant(string $tenantId, int $perPage = 20, int $page = 1): LengthAwarePaginator;
}
