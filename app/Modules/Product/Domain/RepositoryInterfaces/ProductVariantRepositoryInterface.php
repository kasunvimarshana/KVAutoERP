<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\ProductVariant;

interface ProductVariantRepositoryInterface
{
    public function findById(int $id): ?ProductVariant;

    public function findBySku(int $tenantId, string $sku): ?ProductVariant;

    public function findByProduct(int $productId): Collection;

    public function create(array $data): ProductVariant;

    public function update(int $id, array $data): ?ProductVariant;

    public function delete(int $id): bool;
}
