<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Contracts;

interface RevokePermissionServiceInterface
{
    public function execute(int $roleId, int $permissionId): void;
}
