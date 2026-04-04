<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Warehouse\Application\DTOs\CreateLocationData;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;

interface CreateLocationServiceInterface
{
    public function execute(CreateLocationData $data): WarehouseLocation;
}
