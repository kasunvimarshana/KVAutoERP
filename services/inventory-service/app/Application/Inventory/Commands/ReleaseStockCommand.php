<?php

declare(strict_types=1);

namespace App\Application\Inventory\Commands;

/**
 * Command to release reserved stock (compensating transaction in Saga).
 */
final readonly class ReleaseStockCommand
{
    public function __construct(
        public string $productId,
        public string $tenantId,
        public int $quantity,
        public string $orderId,
    ) {}
}
