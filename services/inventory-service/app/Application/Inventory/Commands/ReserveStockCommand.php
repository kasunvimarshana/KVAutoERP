<?php

declare(strict_types=1);

namespace App\Application\Inventory\Commands;

/**
 * Command to reserve stock for an order (used by the Saga).
 */
final readonly class ReserveStockCommand
{
    public function __construct(
        public string $productId,
        public string $tenantId,
        public int $quantity,
        public string $orderId,
    ) {}
}
