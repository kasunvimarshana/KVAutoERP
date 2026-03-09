<?php

declare(strict_types=1);

namespace App\Application\Inventory\Commands;

/**
 * Command to soft-delete a product.
 */
final readonly class DeleteProductCommand
{
    public function __construct(
        public string $productId,
        public string $tenantId,
        public string $performedBy,
    ) {}
}
