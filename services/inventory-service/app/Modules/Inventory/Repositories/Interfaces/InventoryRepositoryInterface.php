<?php

namespace App\Modules\Inventory\Repositories\Interfaces;

use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface
{
    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Inventory;

    public function findByProductId(int $productId): ?Inventory;

    public function findByProductSku(string $sku): ?Inventory;

    public function create(InventoryDTO $dto): Inventory;

    public function update(int $id, InventoryDTO $dto): Inventory;

    public function adjustQuantity(int $id, int $delta): Inventory;

    public function reserveQuantity(int $productId, int $quantity): bool;

    public function releaseReservation(int $productId, int $quantity): bool;

    public function delete(int $id): bool;
}
