<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Contracts;

interface RevokeRoleFromUserServiceInterface
{
    public function execute(int $userId, int $roleId, int $tenantId): void;
}
