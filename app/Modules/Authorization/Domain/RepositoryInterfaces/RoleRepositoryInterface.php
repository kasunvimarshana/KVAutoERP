<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\RepositoryInterfaces;

use Modules\Authorization\Domain\Entities\Permission;
use Modules\Authorization\Domain\Entities\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;

    public function findBySlug(string $slug): ?Role;

    public function findAllByTenant(int $tenantId): array;

    public function save(Role $role): Role;

    public function delete(int $id): void;

    public function assignPermission(int $roleId, int $permissionId): void;

    public function revokePermission(int $roleId, int $permissionId): void;

    /** @return Permission[] */
    public function getPermissions(int $roleId): array;
}
