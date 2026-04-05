<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class StockAdjustmentLine
{
    public function __construct(
        public readonly int $id,
        public readonly int $adjustmentId,
        public readonly int $productId,
        public readonly ?int $productVariantId,
        public readonly float $expectedQty,
        public readonly float $actualQty,
        public readonly float $variance,
        public readonly float $costPerUnit,
        public readonly ?string $batchNumber,
        public readonly ?string $lotNumber,
        public readonly ?string $serialNumber,
        public readonly ?\DateTimeImmutable $expiryDate,
        public readonly ?string $notes,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
