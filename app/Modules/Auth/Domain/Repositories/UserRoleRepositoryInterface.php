<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repositories;

use Modules\Auth\Domain\Entities\UserRole;

interface UserRoleRepositoryInterface
{
    public function assign(int $userId, int $roleId, int $tenantId): UserRole;

    public function revoke(int $userId, int $roleId, int $tenantId): bool;

    public function getUserRoles(int $userId, int $tenantId): array;

    public function getUserPermissions(int $userId, int $tenantId): array;

    public function hasRole(int $userId, int $roleId, int $tenantId): bool;
}
