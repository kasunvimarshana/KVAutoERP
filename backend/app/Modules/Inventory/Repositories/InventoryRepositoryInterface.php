<?php

namespace App\Modules\Inventory\Repositories;

use App\Modules\Inventory\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface
{
    public function findById(string $id, string $tenantId): ?Inventory;

    public function findByProduct(string $productId, string $tenantId): ?Inventory;

    public function paginate(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function create(array $data): Inventory;

    public function update(Inventory $inventory, array $data): Inventory;

    public function delete(Inventory $inventory): bool;

    public function adjustQuantity(Inventory $inventory, int $delta): Inventory;

    public function reserveQuantity(Inventory $inventory, int $quantity): bool;

    public function releaseReservation(Inventory $inventory, int $quantity): bool;
}
