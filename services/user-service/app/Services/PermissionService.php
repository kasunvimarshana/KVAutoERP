<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;

/**
 * Permission application service.
 *
 * Orchestrates permission CRUD within a tenant.
 */
final class PermissionService implements PermissionServiceInterface
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissionRepository,
    ) {}

    /**
     * Create a new permission within a tenant.
     *
     * @param  array<string, mixed>  $data
     * @param  string                $tenantId
     * @param  string                $actorId
     * @return Permission
     *
     * @throws ValidationException
     */
    public function createPermission(array $data, string $tenantId, string $actorId): Permission
    {
        $existing = $this->permissionRepository->findBySlugAndTenant(
            (string) ($data['slug'] ?? ''),
            $tenantId,
        );

        if ($existing !== null) {
            throw new ValidationException('A permission with this slug already exists in the tenant.', [
                'slug' => ['The slug has already been taken.'],
            ]);
        }

        $data['tenant_id']  = $tenantId;
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;
        $data['is_system']  = $data['is_system'] ?? false;

        return $this->permissionRepository->create($data);
    }

    /**
     * Update an existing permission.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @param  string                $actorId
     * @return Permission
     *
     * @throws NotFoundException
     */
    public function updatePermission(string $id, array $data, string $actorId): Permission
    {
        $permission = $this->permissionRepository->findById($id);

        if ($permission === null) {
            throw NotFoundException::for('Permission', $id);
        }

        $data['updated_by'] = $actorId;

        return $this->permissionRepository->update($id, $data);
    }

    /**
     * Delete a permission by UUID.
     *
     * @param  string  $id
     * @return bool
     *
     * @throws NotFoundException
     */
    public function deletePermission(string $id): bool
    {
        $permission = $this->permissionRepository->findById($id);

        if ($permission === null) {
            throw NotFoundException::for('Permission', $id);
        }

        return $this->permissionRepository->delete($id);
    }

    /**
     * Return a paginated list of permissions for a tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<Permission>
     */
    public function listByTenant(string $tenantId, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->permissionRepository->paginateByTenant($tenantId, $perPage, $page);
    }

    /**
     * Find a permission by UUID.
     *
     * @param  string  $id
     * @return Permission|null
     */
    public function findById(string $id): ?Permission
    {
        return $this->permissionRepository->findById($id);
    }
}
