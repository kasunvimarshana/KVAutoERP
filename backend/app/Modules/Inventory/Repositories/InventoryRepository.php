<?php

namespace App\Modules\Inventory\Repositories;

use App\Modules\Inventory\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function findById(string $id, string $tenantId): ?Inventory
    {
        return Inventory::where('id', $id)->where('tenant_id', $tenantId)->first();
    }

    public function findByProduct(string $productId, string $tenantId): ?Inventory
    {
        return Inventory::where('product_id', $productId)->where('tenant_id', $tenantId)->first();
    }

    public function paginate(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Inventory::with('product')->where('tenant_id', $tenantId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['warehouse_location'])) {
            $query->where('warehouse_location', $filters['warehouse_location']);
        }

        if (!empty($filters['low_stock'])) {
            $query->lowStock();
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Inventory
    {
        return Inventory::create($data);
    }

    public function update(Inventory $inventory, array $data): Inventory
    {
        $inventory->update($data);
        return $inventory->fresh();
    }

    public function delete(Inventory $inventory): bool
    {
        return (bool) $inventory->delete();
    }

    public function adjustQuantity(Inventory $inventory, int $delta): Inventory
    {
        return DB::transaction(function () use ($inventory, $delta) {
            $inventory->increment('quantity', $delta);
            $inventory->refresh();
            $inventory->updateStatus();
            $inventory->save();
            return $inventory;
        });
    }

    public function reserveQuantity(Inventory $inventory, int $quantity): bool
    {
        return DB::transaction(function () use ($inventory, $quantity) {
            $inventory->refresh();

            $available = $inventory->quantity - $inventory->reserved_quantity;
            if ($available < $quantity) {
                return false;
            }

            $inventory->increment('reserved_quantity', $quantity);
            return true;
        });
    }

    public function releaseReservation(Inventory $inventory, int $quantity): bool
    {
        return DB::transaction(function () use ($inventory, $quantity) {
            $newReserved = max(0, $inventory->reserved_quantity - $quantity);
            $inventory->update(['reserved_quantity' => $newReserved]);
            return true;
        });
    }
}
