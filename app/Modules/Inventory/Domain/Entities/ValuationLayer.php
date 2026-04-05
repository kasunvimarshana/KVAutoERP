<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class ValuationLayer
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly int $locationId,
        public readonly ?int $batchLotId,
        public readonly float $quantity,
        public readonly float $remainingQuantity,
        public readonly float $unitCost,
        public readonly string $valuationMethod,
        public readonly \DateTimeImmutable $receivedAt,
        public readonly ?string $reference,
        public readonly ?\DateTimeImmutable $createdAt = null,
    ) {}
}
