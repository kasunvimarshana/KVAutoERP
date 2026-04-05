<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class CycleCountLine
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $cycleCountId,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly float $systemQuantity,
        public readonly ?float $countedQuantity,
        public readonly ?int $batchLotId,
    ) {}

    public function getVariance(): ?float
    {
        if ($this->countedQuantity === null) {
            return null;
        }
        return $this->countedQuantity - $this->systemQuantity;
    }
}
