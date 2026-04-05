<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

final class ProductVariant
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly string $sku,
        public readonly ?string $barcode,
        public readonly string $name,
        public readonly ?array $attributes,
        public readonly float $costPrice,
        public readonly float $sellingPrice,
        public readonly ?float $weight,
        public readonly bool $isActive,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
