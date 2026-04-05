<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\StockAdjustment;

interface ReconcileInventoryServiceInterface
{
    public function reconcile(int $adjustmentId, int $postedBy): StockAdjustment;
}
