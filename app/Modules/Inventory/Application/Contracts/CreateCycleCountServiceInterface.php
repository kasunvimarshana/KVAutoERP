<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\CycleCount;

interface CreateCycleCountServiceInterface
{
    public function createForWarehouse(int $tenantId, int $warehouseId, ?int $createdBy): CycleCount;
}
