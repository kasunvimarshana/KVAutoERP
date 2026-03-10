<?php
namespace App\Repositories;
use App\Models\InventoryTransaction;
use App\Repositories\Contracts\InventoryTransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InventoryTransactionRepository extends BaseRepository implements InventoryTransactionRepositoryInterface
{
    public function __construct(InventoryTransaction $model) { parent::__construct($model); }

    public function findByInventory(string $inventoryId): Collection
    {
        return $this->model->where('inventory_id', $inventoryId)->orderBy('created_at', 'desc')->get();
    }

    public function findByReference(string $type, string $id): Collection
    {
        return $this->model->where('reference_type', $type)->where('reference_id', $id)->get();
    }
}
