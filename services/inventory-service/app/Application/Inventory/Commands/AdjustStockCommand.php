<?php

declare(strict_types=1);

namespace App\Application\Inventory\Commands;

/**
 * Command to manually adjust product stock to a new absolute quantity.
 */
final readonly class AdjustStockCommand
{
    public function __construct(
        public string $productId,
        public string $tenantId,
        public int $newQuantity,
        public string $reason,
        public string $performedBy,
    ) {}
}
