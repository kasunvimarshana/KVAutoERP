<?php
namespace App\Repositories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryRepository extends BaseRepository
{
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    public function findByProduct(string $productId): Collection
    {
        return $this->newQuery()->where('product_id', $productId)->get();
    }

    public function findByTenant(string $tenantId): Collection
    {
        return $this->withTenant($tenantId)->all();
    }

    public function adjustStock(string $inventoryId, int $delta): ?Inventory
    {
        $inventory = $this->find($inventoryId);
        if ($inventory === null) {
            return null;
        }
        $inventory->quantity = max(0, $inventory->quantity + $delta);
        $inventory->save();
        return $inventory->fresh();
    }

    public function getLowStock(string $tenantId): Collection
    {
        return $this->withTenant($tenantId)
            ->newQuery()
            ->whereColumn('quantity', '<=', 'min_level')
            ->get();
    }

    public function getWithPagination(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->newQuery()->paginate($perPage, ['*'], 'page', $page);
    }
}
