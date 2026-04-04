<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Contracts;

interface ListRolesServiceInterface
{
    public function execute(int $tenantId): array;
}
