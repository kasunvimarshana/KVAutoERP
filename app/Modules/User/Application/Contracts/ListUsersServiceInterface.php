<?php

declare(strict_types=1);

namespace Modules\User\Application\Contracts;

interface ListUsersServiceInterface
{
    public function execute(int $tenantId, int $page = 1, int $perPage = 15): array;
}
