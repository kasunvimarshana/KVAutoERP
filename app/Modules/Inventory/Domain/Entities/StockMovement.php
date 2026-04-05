<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class StockMovement
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly ?int $fromLocationId,
        public readonly ?int $toLocationId,
        public readonly float $quantity,
        public readonly string $type,
        public readonly ?string $reference,
        public readonly ?string $batchNumber,
        public readonly ?string $lotNumber,
        public readonly ?string $serialNumber,
        public readonly ?\DateTimeImmutable $expiryDate,
        public readonly ?float $cost,
        public readonly ?string $notes,
        public readonly ?int $createdBy,
        public readonly ?\DateTimeImmutable $createdAt = null,
    ) {}
}
