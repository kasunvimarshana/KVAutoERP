<?php

namespace App\Modules\Product\Repositories\Contracts;

use App\Modules\Product\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Product;
    public function create(array $data): Product;
    public function update(int $id, array $data): ?Product;
    public function delete(int $id): bool;
    public function findBySku(string $sku): ?Product;
    public function findByName(string $name): ?Product;
}
