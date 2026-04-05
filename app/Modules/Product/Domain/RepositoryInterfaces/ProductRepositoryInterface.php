<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findBySku(int $tenantId, string $sku): ?Product;

    public function findByBarcode(int $tenantId, string $barcode): ?Product;

    public function findByCategory(int $tenantId, int $categoryId): Collection;

    public function findByTenant(int $tenantId): Collection;

    public function findByType(int $tenantId, string $type): Collection;

    public function create(array $data): Product;

    public function update(int $id, array $data): ?Product;

    public function delete(int $id): bool;

    public function search(string $query, int $tenantId): Collection;
}
