<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class Stock
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly int $locationId,
        public readonly float $quantity,
        public readonly float $reservedQuantity,
        public readonly string $unit,
        public readonly ?\DateTimeImmutable $lastMovementAt,
    ) {}

    public function getAvailableQuantity(): float
    {
        return max(0.0, $this->quantity - $this->reservedQuantity);
    }
}
