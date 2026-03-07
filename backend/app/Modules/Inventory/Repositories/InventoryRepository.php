<?php

namespace App\Modules\Inventory\Repositories;

use App\Modules\Inventory\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function __construct(private readonly Inventory $model) {}

    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('product');

        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['low_stock'])) {
            $query->whereColumn('quantity', '<=', 'min_quantity');
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function find(int $id): Inventory
    {
        return $this->model->with('product')->findOrFail($id);
    }

    public function findByProduct(int $productId): ?Inventory
    {
        return $this->model->where('product_id', $productId)->first();
    }

    public function create(array $data): Inventory
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Inventory
    {
        $inventory = $this->find($id);
        $inventory->update($data);
        return $inventory->fresh('product');
    }

    public function delete(int $id): bool
    {
        $inventory = $this->find($id);
        return $inventory->delete();
    }

    public function adjustQuantity(int $id, int $adjustment): Inventory
    {
        return DB::transaction(function () use ($id, $adjustment) {
            $inventory = $this->model->lockForUpdate()->findOrFail($id);
            $newQuantity = max(0, $inventory->quantity + $adjustment);
            $inventory->update(['quantity' => $newQuantity]);
            return $inventory->fresh('product');
        });
    }
}
