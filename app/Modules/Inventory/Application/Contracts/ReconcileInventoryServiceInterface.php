<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\CycleCount;

interface ReconcileInventoryServiceInterface
{
    /**
     * Apply all variances from a completed cycle count.
     * Returns the number of variance lines applied.
     */
    public function reconcile(int $cycleCountId): int;
}
