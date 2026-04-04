<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Contracts;

interface ListTenantsServiceInterface
{
    public function execute(int $page = 1, int $perPage = 15): array;
}
