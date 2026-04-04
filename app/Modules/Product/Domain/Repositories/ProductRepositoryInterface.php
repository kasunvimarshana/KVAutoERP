<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findBySku(string $sku): ?Product;

    public function findByBarcode(string $barcode): ?Product;

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function findByCategory(int $categoryId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function create(array $data): Product;

    public function update(int $id, array $data): Product;

    public function delete(int $id): bool;
}
