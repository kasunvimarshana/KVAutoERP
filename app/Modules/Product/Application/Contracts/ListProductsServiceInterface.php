<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ListProductsServiceInterface
{
    public function execute(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator;
}
