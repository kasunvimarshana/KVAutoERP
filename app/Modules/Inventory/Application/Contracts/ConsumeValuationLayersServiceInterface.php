<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

interface ConsumeValuationLayersServiceInterface
{
    /**
     * Consume valuation layers using the given method (fifo/lifo/average/fefo).
     *
     * Returns:
     *   ['layers_consumed' => int, 'weighted_avg_cost' => float, 'total_cost' => float]
     */
    public function consume(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $locationId,
        float $quantity,
        string $method,
    ): array;
}
