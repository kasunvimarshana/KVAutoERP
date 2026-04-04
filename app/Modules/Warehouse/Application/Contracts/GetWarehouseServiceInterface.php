<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Warehouse\Domain\Entities\Warehouse;

interface GetWarehouseServiceInterface
{
    public function execute(int $id): Warehouse;
}
