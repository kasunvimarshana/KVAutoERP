<?php

namespace App\Domain\Inventory\Repositories;

use App\Infrastructure\Persistence\BaseRepositoryInterface;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find an inventory item by SKU scoped to a tenant.
     */
    public function findBySku(string $sku, string $tenantId): ?object;

    /**
     * Return all items whose quantity is at or below min_stock_level for a tenant.
     */
    public function getLowStockItems(string $tenantId): mixed;

    /**
     * Update the stock quantity for an item.
     *
     * @param  string  $id        Inventory item ID
     * @param  int     $quantity  The amount to apply
     * @param  string  $operation 'set' | 'increment' | 'decrement'
     */
    public function updateStock(string $id, int $quantity, string $operation = 'set'): bool;

    /**
     * Reserve (lock) the given quantity against an inventory item.
     * Returns false when available stock is insufficient.
     */
    public function reserveStock(string $id, int $quantity): bool;

    /**
     * Release previously reserved stock back to available.
     */
    public function releaseStock(string $id, int $quantity): bool;

    /**
     * Return all items in a given category for a tenant.
     */
    public function getByCategory(string $category, string $tenantId): mixed;
}
