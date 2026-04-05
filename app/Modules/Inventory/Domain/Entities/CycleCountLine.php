<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class CycleCountLine
{
    public function __construct(
        public readonly int $id,
        public readonly int $cycleCountId,
        public readonly int $productId,
        public readonly ?int $productVariantId,
        public readonly float $systemQty,
        public readonly ?float $countedQty,
        public readonly ?float $variance,
        public readonly ?string $notes,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
