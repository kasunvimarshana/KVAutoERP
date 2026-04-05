<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

interface AllocateStockServiceInterface
{
    /**
     * Allocates stock batches by strategy (FEFO/FIFO/LIFO).
     *
     * Returns array of allocations, each containing:
     * ['layer_id', 'batch_number', 'lot_number', 'serial_number', 'expiry_date', 'quantity', 'cost_per_unit']
     */
    public function allocate(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        float $qty,
        string $strategy = 'FEFO',
    ): array;
}
