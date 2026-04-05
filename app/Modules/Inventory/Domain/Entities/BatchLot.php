<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class BatchLot
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly string $batchNumber,
        public readonly ?string $lotNumber,
        public readonly ?string $serialNumber,
        public readonly ?\DateTimeImmutable $expiryDate,
        public readonly ?\DateTimeImmutable $manufacturingDate,
        public readonly float $quantity,
        public readonly float $remainingQuantity,
        public readonly int $locationId,
        public readonly string $status,
        public readonly ?array $metadata,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
