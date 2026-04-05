<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

interface AllocateStockServiceInterface
{
    /**
     * Allocate stock for a product using the given strategy.
     *
     * @return array<int, array{batchId: int, quantity: float, locationId: int|null}>
     */
    public function allocate(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        float $quantity,
        string $strategy,
    ): array;
}
