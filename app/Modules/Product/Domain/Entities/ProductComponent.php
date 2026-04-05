<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

final class ProductComponent
{
    public function __construct(
        public readonly int $id,
        public readonly int $productId,
        public readonly int $componentId,
        public readonly ?int $componentVariantId,
        public readonly float $quantity,
        public readonly string $unitOfMeasure,
        public readonly ?string $notes,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
