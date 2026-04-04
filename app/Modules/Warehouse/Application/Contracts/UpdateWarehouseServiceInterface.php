<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Warehouse\Application\DTOs\UpdateWarehouseData;
use Modules\Warehouse\Domain\Entities\Warehouse;

interface UpdateWarehouseServiceInterface
{
    public function execute(int $id, UpdateWarehouseData $data): Warehouse;
}
