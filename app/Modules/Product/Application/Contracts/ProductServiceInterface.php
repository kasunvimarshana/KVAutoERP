<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\Product;

interface ProductServiceInterface
{
    public function create(array $data): Product;

    public function update(int $id, array $data): Product;

    public function delete(int $id): bool;

    public function find(int $id): Product;

    public function findBySku(int $tenantId, string $sku): Product;

    public function findByBarcode(int $tenantId, string $barcode): Product;

    /** @return Product[] */
    public function search(int $tenantId, string $query): array;

    /** @return Product[] */
    public function findByCategory(int $tenantId, int $categoryId): array;

    /** @return Product[] */
    public function findByType(int $tenantId, string $type): array;

    /** @return Product[] */
    public function all(int $tenantId): array;
}
