<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repositories\Interfaces;

use App\Domain\Inventory\Entities\InventoryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Inventory Repository Interface.
 */
interface InventoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>                          $params
     * @return LengthAwarePaginator|Collection<int, InventoryItem>
     */
    public function all(array $params = []): LengthAwarePaginator|Collection;

    public function find(string $id): ?InventoryItem;

    public function findBySku(string $sku, string $tenantId): ?InventoryItem;

    /** @param  array<string, mixed> $data */
    public function create(array $data): InventoryItem;

    /** @param  array<string, mixed> $data */
    public function update(string $id, array $data): InventoryItem;

    public function delete(string $id): bool;

    /**
     * Reserve stock for a given quantity (for order processing).
     *
     * @throws \DomainException When insufficient stock
     */
    public function reserveStock(string $itemId, int $quantity, string $referenceId): InventoryItem;

    /**
     * Release previously reserved stock (for order cancellation).
     */
    public function releaseStock(string $itemId, int $quantity, string $referenceId): InventoryItem;

    /**
     * Deduct stock when order is fulfilled.
     */
    public function deductStock(string $itemId, int $quantity, string $referenceId): InventoryItem;

    /**
     * Get items with stock at or below reorder point.
     *
     * @param  string $tenantId
     * @return Collection<int, InventoryItem>
     */
    public function getLowStockItems(string $tenantId): Collection;
}
