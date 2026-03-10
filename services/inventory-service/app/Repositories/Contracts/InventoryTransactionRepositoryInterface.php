<?php
namespace App\Repositories\Contracts;
use App\Models\InventoryTransaction;
use Illuminate\Database\Eloquent\Collection;

interface InventoryTransactionRepositoryInterface
{
    public function create(array $data): InventoryTransaction;
    public function findByInventory(string $inventoryId): Collection;
    public function findByReference(string $type, string $id): Collection;
}
