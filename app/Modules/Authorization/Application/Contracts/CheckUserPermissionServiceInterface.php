<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Contracts;

interface CheckUserPermissionServiceInterface
{
    public function execute(int $userId, int $tenantId, string $permissionSlug): bool;
}
