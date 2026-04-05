<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

interface ConsumeValuationLayersServiceInterface
{
    /**
     * Consumes valuation layers for the given qty.
     * Returns weighted average cost per unit consumed.
     */
    public function consume(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        float $qty,
        string $method,
    ): float;
}
