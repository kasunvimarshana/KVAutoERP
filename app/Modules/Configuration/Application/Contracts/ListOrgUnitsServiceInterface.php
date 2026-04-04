<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

interface ListOrgUnitsServiceInterface
{
    public function execute(int $tenantId): array;
}
