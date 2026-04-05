<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\StockServiceInterface;
use Modules\Inventory\Domain\Entities\StockItem;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;

class StockService implements StockServiceInterface
{
    public function __construct(
        private readonly StockItemRepositoryInterface $stockItemRepository,
    ) {}

    public function getStock(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
    ): ?StockItem {
        return $this->stockItemRepository->findByProduct(
            $tenantId,
            $productId,
            $variantId,
            $warehouseId,
            $locationId,
        );
    }

    public function getLowStockItems(int $tenantId, float $threshold): array
    {
        $all = $this->stockItemRepository->all($tenantId);

        return array_values(
            array_filter($all, fn (StockItem $item) => $item->isLowStock($threshold)),
        );
    }

    public function getAllStock(int $tenantId, int $warehouseId): array
    {
        return $this->stockItemRepository->findByWarehouse($tenantId, $warehouseId);
    }
}
