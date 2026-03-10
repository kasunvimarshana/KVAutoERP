<?php
namespace App\Repositories;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    public function __construct(Inventory $model) { parent::__construct($model); }

    protected function searchableColumns(): array { return ['product_code', 'product_name', 'location']; }
    protected function sortableColumns(): array { return ['product_code', 'product_name', 'quantity_available', 'created_at', 'updated_at']; }

    public function findByProductId(string $productId, string $tenantId): ?Inventory
    {
        return $this->model->where('product_id', $productId)->where('tenant_id', $tenantId)->first();
    }

    public function findByProductIds(array $productIds, string $tenantId): Collection
    {
        return $this->model->whereIn('product_id', $productIds)->where('tenant_id', $tenantId)->get();
    }

    /**
     * Reserve stock atomically using pessimistic locking.
     * Throws if insufficient stock.
     */
    public function reserveStock(string $id, int $quantity): Inventory
    {
        return DB::transaction(function () use ($id, $quantity) {
            $inv = $this->model->lockForUpdate()->findOrFail($id);

            if ($inv->quantity_available < $quantity) {
                throw new \RuntimeException(
                    "Insufficient stock. Available: {$inv->quantity_available}, requested: {$quantity}.", 422
                );
            }

            $inv->update([
                'quantity_reserved'  => $inv->quantity_reserved + $quantity,
                'quantity_available' => $inv->quantity_available - $quantity,
            ]);

            return $inv->fresh();
        });
    }

    /**
     * Release reserved stock (compensating action - Saga rollback).
     */
    public function releaseStock(string $id, int $quantity): Inventory
    {
        return DB::transaction(function () use ($id, $quantity) {
            $inv     = $this->model->lockForUpdate()->findOrFail($id);
            $release = min($quantity, $inv->quantity_reserved);

            $inv->update([
                'quantity_reserved'  => max(0, $inv->quantity_reserved - $release),
                'quantity_available' => $inv->quantity_available + $release,
            ]);

            return $inv->fresh();
        });
    }

    /**
     * Confirm stock deduction on order completion.
     */
    public function confirmDeduction(string $id, int $quantity): Inventory
    {
        return DB::transaction(function () use ($id, $quantity) {
            $inv = $this->model->lockForUpdate()->findOrFail($id);

            $inv->update([
                'quantity_on_hand'  => max(0, $inv->quantity_on_hand - $quantity),
                'quantity_reserved' => max(0, $inv->quantity_reserved - $quantity),
            ]);

            return $inv->fresh();
        });
    }

    /**
     * Manual stock adjustment (add, remove, or set).
     */
    public function adjustStock(string $id, int $quantity, string $type): Inventory
    {
        return DB::transaction(function () use ($id, $quantity, $type) {
            $inv = $this->model->lockForUpdate()->findOrFail($id);

            $newOnHand = match ($type) {
                'add'    => $inv->quantity_on_hand + $quantity,
                'remove' => max(0, $inv->quantity_on_hand - $quantity),
                'set'    => $quantity,
                default  => throw new \InvalidArgumentException("Invalid adjustment type: {$type}"),
            };

            $inv->update([
                'quantity_on_hand'   => $newOnHand,
                'quantity_available' => max(0, $newOnHand - $inv->quantity_reserved),
            ]);

            return $inv->fresh();
        });
    }
}
