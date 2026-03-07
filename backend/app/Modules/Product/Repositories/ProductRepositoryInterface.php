<?php

namespace App\Modules\Product\Repositories;

interface ProductRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15);
    public function find(int $id);
    public function create(array $data): \App\Modules\Product\Models\Product;
    public function update(int $id, array $data): \App\Modules\Product\Models\Product;
    public function delete(int $id): bool;
    public function findBySku(string $sku, int $tenantId): ?\App\Modules\Product\Models\Product;
    public function findByTenant(int $tenantId, array $filters = [], int $perPage = 15);
}
