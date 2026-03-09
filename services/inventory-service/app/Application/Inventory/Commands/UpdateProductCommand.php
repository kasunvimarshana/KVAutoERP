<?php

declare(strict_types=1);

namespace App\Application\Inventory\Commands;

/**
 * Command to update an existing product.
 */
final readonly class UpdateProductCommand
{
    public function __construct(
        public string $productId,
        public string $tenantId,
        public string $performedBy,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $categoryId = null,
        public ?float $price = null,
        public ?float $costPrice = null,
        public ?string $currency = null,
        public ?int $minStockLevel = null,
        public ?int $maxStockLevel = null,
        public ?string $unit = null,
        public ?string $barcode = null,
        public ?array $tags = null,
        public ?array $attributes = null,
        public ?string $status = null,
        public ?bool $isActive = null,
    ) {}
}
