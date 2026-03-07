<?php

namespace App\Modules\Inventory\Repositories;

interface InventoryRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15);
    public function find(int $id);
    public function findByProduct(int $productId): ?\App\Modules\Inventory\Models\Inventory;
    public function create(array $data): \App\Modules\Inventory\Models\Inventory;
    public function update(int $id, array $data): \App\Modules\Inventory\Models\Inventory;
    public function delete(int $id): bool;
    public function adjustQuantity(int $id, int $adjustment): \App\Modules\Inventory\Models\Inventory;
}
