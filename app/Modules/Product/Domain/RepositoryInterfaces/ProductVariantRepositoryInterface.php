<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Modules\Product\Domain\Entities\ProductVariant;

interface ProductVariantRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?ProductVariant;

    public function findBySku(string $sku, int $tenantId): ?ProductVariant;

    public function findByProduct(int $productId, int $tenantId): array;

    public function allByTenant(int $tenantId): array;

    public function create(array $data): ProductVariant;

    public function update(int $id, array $data): ProductVariant;

    public function delete(int $id, int $tenantId): bool;
}
