<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ListTenantsServiceInterface
{
    public function execute(array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator;
}
