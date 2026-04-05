<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\InventoryAdjustment;

interface ReconcileInventoryServiceInterface
{
    public function reconcile(int $tenantId, int $adjustmentId): InventoryAdjustment;
}
