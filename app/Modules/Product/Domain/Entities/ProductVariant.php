<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class ProductVariant
{
    public function __construct(
        public ?int $id,
        public int $productId,
        public string $name,
        public string $sku,
        public ?string $barcode,
        public ?array $attributes,
        public bool $isActive,
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {}
}
