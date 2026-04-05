<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Illuminate\Support\Collection;

interface StockServiceInterface
{
    public function getStock(int $productId, ?int $variantId, ?int $locationId): Collection;

    public function getTotalStock(int $tenantId, int $productId, ?int $variantId = null): float;

    public function adjustStock(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $quantity,
        string $type,
        ?string $referenceType,
        ?int $referenceId,
    ): void;
}
