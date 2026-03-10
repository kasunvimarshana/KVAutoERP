<?php
namespace App\Repositories\Contracts;
use App\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface InventoryRepositoryInterface
{
    public function all(array $filters = [], array $params = []): LengthAwarePaginator|Collection;
    public function findById(string $id): ?Inventory;
    public function findByProductId(string $productId, string $tenantId): ?Inventory;
    public function findByProductIds(array $productIds, string $tenantId): Collection;
    public function create(array $data): Inventory;
    public function update(string $id, array $data): Inventory;
    public function delete(string $id): bool;
    public function reserveStock(string $id, int $quantity): Inventory;
    public function releaseStock(string $id, int $quantity): Inventory;
    public function confirmDeduction(string $id, int $quantity): Inventory;
    public function adjustStock(string $id, int $quantity, string $type): Inventory;
}
