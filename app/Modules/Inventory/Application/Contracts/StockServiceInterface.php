<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\StockItem;

interface StockServiceInterface
{
    public function getStock(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
    ): ?StockItem;

    public function getLowStockItems(int $tenantId, float $threshold): array;

    public function getAllStock(int $tenantId, int $warehouseId): array;
}
