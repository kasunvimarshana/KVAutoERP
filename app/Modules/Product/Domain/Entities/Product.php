<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class Product
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $sku,
        public readonly string $name,
        public readonly string $type,
        public readonly ?int $categoryId,
        public readonly ?string $description,
        public readonly bool $isActive,
        public readonly string $unitOfMeasure,
        public readonly ?float $weight,
        public readonly ?array $dimensions,
        public readonly ?array $images,
        public readonly ?array $metadata,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
