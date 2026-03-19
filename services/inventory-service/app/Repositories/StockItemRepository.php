<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\StockItemRepositoryInterface;
use App\Models\StockItem;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;
use KvEnterprise\SharedKernel\Exceptions\DomainException;

/**
 * Eloquent-backed stock item repository.
 *
 * Handles atomic quantity mutations with BCMath precision.
 * All reads are tenant-scoped via TenantAwareModel's global scope.
 */
final class StockItemRepository implements StockItemRepositoryInterface
{
    /**
     * Return paginated stock items.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<StockItem>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator
    {
        $query = StockItem::with(['warehouse', 'bin', 'lot']);

        if ($filter !== null) {
            foreach (['product_id', 'warehouse_id', 'bin_id', 'lot_id'] as $column) {
                if (isset($filter->filters[$column]) && $filter->filters[$column] !== '') {
                    $query->where($column, $filter->filters[$column]);
                }
            }

            foreach ($filter->sorts as $sort) {
                $query->orderBy($sort['field'], $sort['direction']);
            }
        }

        if ($query->getQuery()->orders === null) {
            $query->orderBy('product_id')->orderBy('warehouse_id');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find the stock item for a specific product/warehouse/bin/lot.
     *
     * @param  string       $productId
     * @param  string       $warehouseId
     * @param  string|null  $binId
     * @param  string|null  $lotId
     * @param  bool         $lock
     * @return StockItem|null
     */
    public function findByLocation(
        string $productId,
        string $warehouseId,
        ?string $binId = null,
        ?string $lotId = null,
        bool $lock = false,
    ): ?StockItem {
        $query = StockItem::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($binId !== null) {
            $query->where('bin_id', $binId);
        } else {
            $query->whereNull('bin_id');
        }

        if ($lotId !== null) {
            $query->where('lot_id', $lotId);
        } else {
            $query->whereNull('lot_id');
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    /**
     * Return all stock items for a product.
     *
     * @param  string  $productId
     * @return array<int, StockItem>
     */
    public function findByProduct(string $productId): array
    {
        return StockItem::with(['warehouse', 'bin', 'lot'])
            ->where('product_id', $productId)
            ->get()
            ->all();
    }

    /**
     * Upsert a stock item record.
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
    ): StockItem {
        $item = $this->findByLocation($productId, $warehouseId, $binId, $lotId);

        if ($item === null) {
            $qtyOnHand  = $data['qty_on_hand'] ?? '0.0000';
            $qtyReserved = $data['qty_reserved'] ?? '0.0000';
            $item = StockItem::create(array_merge($data, [
                'product_id'    => $productId,
                'warehouse_id'  => $warehouseId,
                'bin_id'        => $binId,
                'lot_id'        => $lotId,
                'qty_on_hand'   => $qtyOnHand,
                'qty_reserved'  => $qtyReserved,
                'qty_available' => bcsub($qtyOnHand, $qtyReserved, 4),
            ]));
        } else {
            $item->update($data);
            $item = $item->fresh() ?? $item;
        }

        return $item;
    }

    /**
     * Increment on-hand quantity using BCMath arithmetic.
     *
     * @param  StockItem  $item
     * @param  string     $qty
     * @return StockItem
     */
    public function incrementOnHand(StockItem $item, string $qty): StockItem
    {
        $newQty = bcadd($item->qty_on_hand, $qty, 4);
        $newAvailable = bcsub($newQty, $item->qty_reserved, 4);
        $item->update(['qty_on_hand' => $newQty, 'qty_available' => $newAvailable, 'version' => $item->version + 1]);

        return $item->fresh() ?? $item;
    }

    /**
     * Decrement on-hand quantity using BCMath arithmetic.
     *
     * @param  StockItem  $item
     * @param  string     $qty
     * @return StockItem
     *
     * @throws DomainException
     */
    public function decrementOnHand(StockItem $item, string $qty): StockItem
    {
        if (bccomp($item->qty_available, $qty, 4) < 0) {
            throw new DomainException(
                "Insufficient available stock. Available: {$item->qty_available}, Requested: {$qty}",
            );
        }

        $newQty = bcsub($item->qty_on_hand, $qty, 4);
        $newAvailable = bcsub($newQty, $item->qty_reserved, 4);
        $item->update(['qty_on_hand' => $newQty, 'qty_available' => $newAvailable, 'version' => $item->version + 1]);

        return $item->fresh() ?? $item;
    }

    /**
     * Increment reserved quantity.
     *
     * @param  StockItem  $item
     * @param  string     $qty
     * @return StockItem
     */
    public function incrementReserved(StockItem $item, string $qty): StockItem
    {
        $newReserved = bcadd($item->qty_reserved, $qty, 4);
        $newAvailable = bcsub($item->qty_on_hand, $newReserved, 4);
        $item->update(['qty_reserved' => $newReserved, 'qty_available' => $newAvailable, 'version' => $item->version + 1]);

        return $item->fresh() ?? $item;
    }

    /**
     * Decrement reserved quantity.
     *
     * @param  StockItem  $item
     * @param  string     $qty
     * @return StockItem
     */
    public function decrementReserved(StockItem $item, string $qty): StockItem
    {
        $newReserved = bcsub($item->qty_reserved, $qty, 4);
        // Clamp to zero to avoid negative reserved quantities.
        if (bccomp($newReserved, '0', 4) < 0) {
            $newReserved = '0.0000';
        }
        $newAvailable = bcsub($item->qty_on_hand, $newReserved, 4);

        $item->update(['qty_reserved' => $newReserved, 'qty_available' => $newAvailable, 'version' => $item->version + 1]);

        return $item->fresh() ?? $item;
    }
}
