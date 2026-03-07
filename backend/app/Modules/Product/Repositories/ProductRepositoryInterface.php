<?php

namespace App\Modules\Product\Repositories;

use App\Modules\Product\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function findById(string $id, string $tenantId): ?Product;

    public function findBySku(string $sku, string $tenantId): ?Product;

    public function paginate(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;

    public function restore(string $id): bool;
}
