<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

interface GetUserPermissionsServiceInterface
{
    public function execute(int $userId, int $tenantId): array;
}
