<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\Product;

interface ProductServiceInterface
{
    public function createProduct(array $data): Product;

    public function updateProduct(int $id, array $data): Product;

    public function deleteProduct(int $id, int $tenantId): bool;

    public function getProduct(int $id, int $tenantId): Product;

    public function getAll(int $tenantId): array;

    public function getByCategory(int $categoryId, int $tenantId): array;

    public function getByType(string $type, int $tenantId): array;
}
