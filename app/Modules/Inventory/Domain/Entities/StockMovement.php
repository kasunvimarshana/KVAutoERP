<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class StockMovement
{
    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_ISSUE = 'issue';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_RETURN = 'return';
    public const TYPE_CYCLE_COUNT = 'cycle_count';
    public const TYPES = [
        self::TYPE_RECEIPT,
        self::TYPE_ISSUE,
        self::TYPE_TRANSFER,
        self::TYPE_ADJUSTMENT,
        self::TYPE_RETURN,
        self::TYPE_CYCLE_COUNT,
    ];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly ?int $productVariantId,
        public readonly ?int $fromLocationId,
        public readonly ?int $toLocationId,
        public readonly float $quantity,
        public readonly string $type,
        public readonly ?string $referenceType,
        public readonly ?int $referenceId,
        public readonly ?string $batchNumber,
        public readonly ?string $lotNumber,
        public readonly ?string $serialNumber,
        public readonly ?\DateTimeImmutable $expiryDate,
        public readonly float $costPerUnit,
        public readonly ?string $notes,
        public readonly ?int $movedBy,
        public readonly \DateTimeImmutable $movedAt,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
