<?php

declare(strict_types=1);

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Entities\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function findById(string $id): ?Product;

    public function findBySku(string $sku, string $tenantId): ?Product;

    public function findByTenant(string $tenantId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByCategory(string $categoryId, string $tenantId, array $filters = []): Collection;

    public function create(array $data): Product;

    public function update(string $id, array $data): Product;

    public function delete(string $id): bool;

    public function search(string $term, string $tenantId, int $perPage = 15): LengthAwarePaginator;

    public function findWithLowStock(string $tenantId): Collection;

    public function findAll(array $filters = [], array $options = []): mixed;

    public function skuExists(string $sku, string $tenantId, ?string $excludeId = null): bool;
}
