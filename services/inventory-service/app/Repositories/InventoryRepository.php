<?php

namespace App\Repositories;

use App\Models\Stock;
use App\Models\Lot;
use Shared\Core\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryRepository extends BaseRepository
{
    public function model(): string
    {
        return Stock::class;
    }

    /**
     * Get available stock for a product using FEFO (First-Expired, First-Out)
     *
     * @param int $productId
     * @param float $quantity
     * @return Collection
     */
    public function getAvailableStockFEFO(int $productId, float $quantity)
    {
        return $this->query
            ->where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->where('status', 'Available')
            ->join('lots', 'stocks.lot_id', '=', 'lots.id')
            ->orderBy('lots.expiry_date', 'asc')
            ->select('stocks.*')
            ->get();
    }

    /**
     * Update stock levels and record transaction
     *
     * @param int $productId
     * @param int $warehouseId
     * @param float $quantity
     * @param string $type // IN, OUT, ADJUSTMENT
     * @param string|null $reference
     * @return Stock
     */
    public function updateStock(int $productId, int $warehouseId, float $quantity, string $type, ?string $reference = null)
    {
        return DB::transaction(function() use ($productId, $warehouseId, $quantity, $type, $reference) {
            $stock = $this->model->firstOrCreate([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ], ['quantity' => 0]);

            if ($type === 'IN') {
                $stock->increment('quantity', $quantity);
            } elseif ($type === 'OUT') {
                $stock->decrement('quantity', $quantity);
            }

            $stock->transactions()->create([
                'type' => $type,
                'quantity' => $quantity,
                'reference' => $reference,
            ]);

            return $stock;
        });
    }
}
