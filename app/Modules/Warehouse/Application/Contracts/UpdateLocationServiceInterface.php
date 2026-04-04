<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Warehouse\Application\DTOs\UpdateLocationData;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;

interface UpdateLocationServiceInterface
{
    public function execute(int $id, UpdateLocationData $data): WarehouseLocation;
}
