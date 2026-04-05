<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Modules\Product\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Product;

    public function findBySku(string $sku, int $tenantId): ?Product;

    public function allByTenant(int $tenantId): array;

    public function findByCategory(int $categoryId, int $tenantId): array;

    public function findByType(string $type, int $tenantId): array;

    public function create(array $data): Product;

    public function update(int $id, array $data): Product;

    public function delete(int $id, int $tenantId): bool;
}
