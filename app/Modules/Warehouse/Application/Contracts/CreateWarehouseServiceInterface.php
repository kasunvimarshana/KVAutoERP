<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Warehouse\Application\DTOs\CreateWarehouseData;
use Modules\Warehouse\Domain\Entities\Warehouse;

interface CreateWarehouseServiceInterface
{
    public function execute(CreateWarehouseData $data): Warehouse;
}
