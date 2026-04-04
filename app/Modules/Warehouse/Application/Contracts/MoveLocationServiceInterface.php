<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Warehouse\Application\DTOs\MoveLocationData;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;

interface MoveLocationServiceInterface
{
    public function execute(MoveLocationData $data): WarehouseLocation;
}
