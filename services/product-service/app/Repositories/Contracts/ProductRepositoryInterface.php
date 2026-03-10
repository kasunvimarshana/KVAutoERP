<?php
namespace App\Repositories\Contracts;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function all(array $filters = [], array $params = []): LengthAwarePaginator|Collection;
    public function findById(string $id): ?Product;
    public function findByCode(string $code, string $tenantId): ?Product;
    public function findByIds(array $ids, string $tenantId): Collection;
    public function findByCodes(array $codes, string $tenantId): Collection;
    public function create(array $data): Product;
    public function update(string $id, array $data): Product;
    public function delete(string $id): bool;
}
