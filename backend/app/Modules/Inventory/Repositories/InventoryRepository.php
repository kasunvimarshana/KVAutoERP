<?php

namespace App\Modules\Inventory\Repositories;

use App\Core\Repository\BaseRepository;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Collection;

class InventoryRepository extends BaseRepository
{
    public function __construct(Inventory $inventory)
    {
        parent::__construct($inventory);
    }

    public function findByProduct(int $productId): Collection
    {
        return $this->model->where('product_id', $productId)->with('product')->get();
    }

    public function findByWarehouse(string $warehouse): Collection
    {
        return $this->model->where('warehouse', $warehouse)->with('product')->get();
    }
}
