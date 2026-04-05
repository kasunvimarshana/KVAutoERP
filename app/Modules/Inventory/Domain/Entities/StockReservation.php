<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class StockReservation
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly int $locationId,
        public readonly float $quantity,
        public readonly string $referenceType,
        public readonly int $referenceId,
        public readonly ?\DateTimeImmutable $expiresAt,
        public readonly string $status,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
