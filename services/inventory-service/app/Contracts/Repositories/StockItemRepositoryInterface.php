<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\StockItem;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Contract for the stock item repository.
 *
 * StockItem represents the current on-hand quantity at a specific
 * product / warehouse / bin / lot combination.
 */
interface StockItemRepositoryInterface
{
    /**
     * Return paginated stock items.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<StockItem>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator;

    /**
     * Find the stock item for a specific product/warehouse/bin/lot.
     * Applies a pessimistic lock when $lock is true.
     *
     * @param  string       $productId
     * @param  string       $warehouseId
     * @param  string|null  $binId
     * @param  string|null  $lotId
     * @param  bool         $lock  Acquire SELECT FOR UPDATE lock.
     * @return StockItem|null
     */
    public function findByLocation(
        string $productId,
        string $warehouseId,
        ?string $binId = null,
        ?string $lotId = null,
        bool $lock = false,
    ): ?StockItem;

    /**
     * Return all stock items for a product across all warehouses.
     *
     * @param  string  $productId
     * @return array<int, StockItem>
     */
    public function findByProduct(string $productId): array;

    /**
     * Upsert a stock item record (create or update by location key).
     *
     * @param  string                $productId
     * @param  string                $warehouseId
     * @param  string|null           $binId
     * @param  string|null           $lotId
     * @param  array<string, mixed>  $data
     * @return StockItem
     */
    public function upsert(
        string $productId,
        string $warehouseId,
        ?string $binId,
        ?string $lotId,
        array $data,
    ): StockItem;

    /**
     * Increment on-hand quantity using BCMath arithmetic.
     *
     * @param  StockItem  $item
     * @param  string     $qty  Positive BCMath string.
     * @return StockItem
     */
    public function incrementOnHand(StockItem $item, string $qty): StockItem;

    /**
     * Decrement on-hand quantity using BCMath arithmetic.
     *
     * @param  StockItem  $item
     * @param  string     $qty  Positive BCMath string.
     * @return StockItem
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\DomainException  On insufficient stock.
     */
    public function decrementOnHand(StockItem $item, string $qty): StockItem;

    /**
     * Increment reserved quantity.
     *
     * @param  StockItem  $item
     * @param  string     $qty
     * @return StockItem
     */
    public function incrementReserved(StockItem $item, string $qty): StockItem;

    /**
     * Decrement reserved quantity.
     *
     * @param  StockItem  $item
     * @param  string     $qty
     * @return StockItem
     */
    public function decrementReserved(StockItem $item, string $qty): StockItem;
}
