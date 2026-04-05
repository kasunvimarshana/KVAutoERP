<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

interface AllocateStockServiceInterface
{
    /**
     * Allocate stock from batch/lots using the given method (fifo/lifo/fefo).
     *
     * Returns:
     *   ['allocations' => [['batchLotId'=>int, 'quantity'=>float, 'locationId'=>int, 'expiryDate'=>?string]], 'total_allocated' => float]
     */
    public function allocate(
        int $tenantId,
        int $productId,
        ?int $variantId,
        float $quantity,
        string $method,
    ): array;
}
