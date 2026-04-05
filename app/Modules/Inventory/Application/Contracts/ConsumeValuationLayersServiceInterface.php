<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

interface ConsumeValuationLayersServiceInterface
{
    /**
     * Consume valuation layers for the given quantity and method.
     * Returns the weighted average unit cost of consumed layers.
     */
    public function consume(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        string $method,
        float $quantity,
    ): float;
}
