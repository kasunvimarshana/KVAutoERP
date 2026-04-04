<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

interface RevokeUserRoleServiceInterface
{
    public function execute(int $userId, int $roleId, int $tenantId): bool;
}
