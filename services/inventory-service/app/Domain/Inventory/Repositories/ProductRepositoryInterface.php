<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\Product;
use App\Domain\Inventory\Entities\StockMovement;
use App\Shared\Contracts\RepositoryInterface;

/**
 * Product repository contract.
 */
interface ProductRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a product by SKU within a tenant.
     */
    public function findBySku(string $sku, string $tenantId): ?Product;

    /**
     * Find products in a category with optional filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Product[]
     */
    public function findByCategory(string $categoryId, string $tenantId, array $filters = []): array;

    /**
     * Return all products at or below their minimum stock level.
     *
     * @return Product[]
     */
    public function findLowStock(string $tenantId): array;

    /**
     * Return all products with zero stock.
     *
     * @return Product[]
     */
    public function findOutOfStock(string $tenantId): array;

    /**
     * Apply a stock change to a product and record the movement.
     *
     * @param  string  $productId
     * @param  int     $quantity    New absolute quantity (for ADJUSTMENT) or delta.
     * @param  string  $type        StockMovementType value.
     * @param  string  $reference   External reference (e.g., order ID).
     * @param  string  $reason      Human-readable reason.
     * @param  string  $performedBy Actor identifier.
     */
    public function updateStock(
        string $productId,
        int $quantity,
        string $type,
        string $reference,
        string $reason,
        string $performedBy,
    ): StockMovement;

    /**
     * Atomically reserve stock for an order.
     *
     * @throws \RuntimeException When insufficient stock.
     */
    public function reserveStock(string $productId, int $quantity, string $orderId): bool;

    /**
     * Release previously-reserved stock for an order.
     */
    public function releaseStock(string $productId, int $quantity, string $orderId): bool;

    /**
     * Bulk-update prices for multiple products within a tenant.
     *
     * @param  array<array{product_id: string, price: float, cost_price?: float}>  $updates
     * @return int  Number of records updated.
     */
    public function bulkUpdatePrices(array $updates, string $tenantId): int;
}
