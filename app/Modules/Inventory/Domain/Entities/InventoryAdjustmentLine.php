<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class InventoryAdjustmentLine
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $adjustmentId,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly float $expectedQuantity,
        public readonly float $actualQuantity,
        public readonly float $unitCost,
        public readonly ?int $batchLotId,
    ) {}

    public function getVariance(): float
    {
        return $this->actualQuantity - $this->expectedQuantity;
    }
}
