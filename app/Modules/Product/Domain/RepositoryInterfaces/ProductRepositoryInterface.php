<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Modules\Product\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findBySku(string $sku): ?Product;
    public function findByBarcode(string $barcode): ?Product;
    public function findAllByTenant(int $tenantId, int $page = 1, int $perPage = 15): array;
    public function findByCategory(int $categoryId, int $page = 1, int $perPage = 15): array;
    public function save(Product $product): Product;
    public function delete(int $id): void;
}
