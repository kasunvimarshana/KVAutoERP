<?php

declare(strict_types=1);

namespace App\Application\Inventory\Commands;

/**
 * Command to create a new product.
 */
final readonly class CreateProductCommand
{
    public function __construct(
        public string $tenantId,
        public string $sku,
        public string $name,
        public string $description,
        public ?string $categoryId,
        public float $price,
        public float $costPrice,
        public string $currency,
        public int $stockQuantity,
        public int $minStockLevel,
        public int $maxStockLevel,
        public string $unit,
        public ?string $barcode,
        public array $tags,
        public array $attributes,
        public string $performedBy,
        public string $status = 'active',
    ) {}
}
