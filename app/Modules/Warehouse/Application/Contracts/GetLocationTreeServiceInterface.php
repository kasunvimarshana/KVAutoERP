<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

interface GetLocationTreeServiceInterface
{
    public function execute(int $warehouseId): array;
}
