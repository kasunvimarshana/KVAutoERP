<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class StockReservation
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly ?int $productVariantId,
        public readonly ?int $locationId,
        public readonly float $quantity,
        public readonly string $referenceType,
        public readonly int $referenceId,
        public readonly ?\DateTimeImmutable $expiresAt,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
