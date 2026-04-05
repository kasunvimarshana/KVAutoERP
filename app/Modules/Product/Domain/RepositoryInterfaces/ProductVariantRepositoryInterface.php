<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Modules\Product\Domain\Entities\ProductVariant;

interface ProductVariantRepositoryInterface
{
    public function findById(int $id): ?ProductVariant;

    /** @return ProductVariant[] */
    public function findByProduct(int $productId): array;

    public function findBySku(int $tenantId, string $sku): ?ProductVariant;

    public function create(array $data): ProductVariant;

    public function update(int $id, array $data): ?ProductVariant;

    public function delete(int $id): bool;
}
