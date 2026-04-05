<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class ValuationLayer
{
    public const METHOD_FIFO = 'fifo';
    public const METHOD_LIFO = 'lifo';
    public const METHOD_AVERAGE = 'average';
    public const METHODS = [self::METHOD_FIFO, self::METHOD_LIFO, self::METHOD_AVERAGE];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly ?int $productVariantId,
        public readonly int $warehouseId,
        public readonly ?string $batchNumber,
        public readonly ?string $lotNumber,
        public readonly ?string $serialNumber,
        public readonly \DateTimeImmutable $receivedAt,
        public readonly ?\DateTimeImmutable $expiryDate,
        public readonly float $quantityReceived,
        public readonly float $quantityRemaining,
        public readonly float $costPerUnit,
        public readonly string $valuationMethod,
        public readonly bool $isExhausted,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
