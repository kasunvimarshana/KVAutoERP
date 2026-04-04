<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\RepositoryInterfaces;

use Modules\Authorization\Domain\Entities\Role;

interface UserRoleRepositoryInterface
{
    public function assignRole(int $userId, int $roleId, int $tenantId): void;

    public function revokeRole(int $userId, int $roleId, int $tenantId): void;

    /** @return Role[] */
    public function getUserRoles(int $userId, int $tenantId): array;

    public function hasPermission(int $userId, int $tenantId, string $permissionSlug): bool;

    public function hasRole(int $userId, int $tenantId, string $roleSlug): bool;
}
