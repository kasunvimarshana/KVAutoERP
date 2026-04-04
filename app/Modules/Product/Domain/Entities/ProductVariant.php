<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class ProductVariant
{
    public function __construct(
        public readonly int $id,
        public int $tenantId,
        public int $productId,
        public string $name,
        public string $sku,
        public ?string $barcode,
        public array $attributes,
        public ?float $price,
        public ?float $cost,
        public ?float $weight,
        public bool $isActive,
        public bool $stockManagement,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
    ) {}
}
