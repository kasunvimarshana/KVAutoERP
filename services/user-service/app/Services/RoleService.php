<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;

/**
 * Role application service.
 *
 * Orchestrates role CRUD and permission assignment within a tenant.
 */
final class RoleService implements RoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * Create a new role within a tenant.
     *
     * @param  array<string, mixed>  $data
     * @param  string                $tenantId
     * @param  string                $actorId
     * @return Role
     *
     * @throws ValidationException
     */
    public function createRole(array $data, string $tenantId, string $actorId): Role
    {
        $existing = $this->roleRepository->findBySlugAndTenant(
            (string) ($data['slug'] ?? ''),
            $tenantId,
        );

        if ($existing !== null) {
            throw new ValidationException('A role with this slug already exists in the tenant.', [
                'slug' => ['The slug has already been taken.'],
            ]);
        }

        $data['tenant_id']  = $tenantId;
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;
        $data['is_system']  = $data['is_system'] ?? false;

        return $this->roleRepository->create($data);
    }

    /**
     * Update an existing role.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @param  string                $actorId
     * @return Role
     *
     * @throws NotFoundException
     */
    public function updateRole(string $id, array $data, string $actorId): Role
    {
        $role = $this->roleRepository->findById($id);

        if ($role === null) {
            throw NotFoundException::for('Role', $id);
        }

        $data['updated_by'] = $actorId;

        return $this->roleRepository->update($id, $data);
    }

    /**
     * Delete a role by UUID.
     *
     * @param  string  $id
     * @return bool
     *
     * @throws NotFoundException
     */
    public function deleteRole(string $id): bool
    {
        $role = $this->roleRepository->findById($id);

        if ($role === null) {
            throw NotFoundException::for('Role', $id);
        }

        return $this->roleRepository->delete($id);
    }

    /**
     * Assign a permission to a role.
     *
     * @param  string  $roleId
     * @param  string  $permissionId
     * @return void
     *
     * @throws NotFoundException
     */
    public function assignPermission(string $roleId, string $permissionId): void
    {
        $role = $this->roleRepository->findById($roleId);

        if ($role === null) {
            throw NotFoundException::for('Role', $roleId);
        }

        $this->roleRepository->assignPermission($roleId, $permissionId);
    }

    /**
     * Revoke a permission from a role.
     *
     * @param  string  $roleId
     * @param  string  $permissionId
     * @return void
     *
     * @throws NotFoundException
     */
    public function revokePermission(string $roleId, string $permissionId): void
    {
        $role = $this->roleRepository->findById($roleId);

        if ($role === null) {
            throw NotFoundException::for('Role', $roleId);
        }

        $this->roleRepository->revokePermission($roleId, $permissionId);
    }

    /**
     * Return a paginated list of roles for a tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<Role>
     */
    public function listByTenant(string $tenantId, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->roleRepository->paginateByTenant($tenantId, $perPage, $page);
    }

    /**
     * Find a role by UUID.
     *
     * @param  string  $id
     * @return Role|null
     */
    public function findById(string $id): ?Role
    {
        return $this->roleRepository->findById($id);
    }
}
