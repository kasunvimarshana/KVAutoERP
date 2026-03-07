<?php

namespace App\Modules\Inventory\Repositories;

use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Repositories\Interfaces\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function __construct(
        private readonly Inventory $model
    ) {}

    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['warehouse_location'])) {
            $query->inWarehouse($filters['warehouse_location']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->lowStock();
        }

        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $query->inStock();
        }

        if (!empty($filters['search'])) {
            $query->where('product_sku', 'LIKE', "%{$filters['search']}%");
        }

        $sortField     = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $allowedSorts  = ['quantity', 'warehouse_location', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Inventory
    {
        return $this->model->find($id);
    }

    public function findByProductId(int $productId): ?Inventory
    {
        return $this->model->where('product_id', $productId)->first();
    }

    public function findByProductSku(string $sku): ?Inventory
    {
        return $this->model->where('product_sku', $sku)->first();
    }

    public function create(InventoryDTO $dto): Inventory
    {
        return $this->model->create($dto->toArray());
    }

    public function update(int $id, InventoryDTO $dto): Inventory
    {
        $inventory = $this->model->findOrFail($id);
        $inventory->update($dto->toArray());
        return $inventory->fresh();
    }

    public function adjustQuantity(int $id, int $delta): Inventory
    {
        $inventory = $this->model->findOrFail($id);
        $inventory->increment('quantity', $delta);
        return $inventory->fresh();
    }

    public function reserveQuantity(int $productId, int $quantity): bool
    {
        $inventory = $this->findByProductId($productId);

        if (!$inventory || $inventory->available_quantity < $quantity) {
            return false;
        }

        $inventory->increment('reserved_quantity', $quantity);
        return true;
    }

    public function releaseReservation(int $productId, int $quantity): bool
    {
        $inventory = $this->findByProductId($productId);

        if (!$inventory) {
            return false;
        }

        $releaseAmount = min($quantity, $inventory->reserved_quantity);
        if ($releaseAmount > 0) {
            $inventory->decrement('reserved_quantity', $releaseAmount);
        }

        return true;
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->destroy($id);
    }
}
