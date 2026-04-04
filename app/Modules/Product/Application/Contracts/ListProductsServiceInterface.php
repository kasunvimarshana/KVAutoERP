<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

interface ListProductsServiceInterface
{
    public function execute(int $tenantId, int $page = 1, int $perPage = 15): array;
}
