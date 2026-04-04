<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

interface SyncRolePermissionsServiceInterface
{
    public function execute(int $roleId, array $permissionIds): void;
}
