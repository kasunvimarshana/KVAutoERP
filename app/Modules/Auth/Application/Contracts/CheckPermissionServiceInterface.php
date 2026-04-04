<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

interface CheckPermissionServiceInterface
{
    public function execute(int $userId, string $ability, int $tenantId): bool;
}
