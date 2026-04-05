<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\ProductVariant;

interface ProductVariantServiceInterface
{
    public function createVariant(array $data): ProductVariant;

    public function updateVariant(int $id, array $data): ProductVariant;

    public function deleteVariant(int $id, int $tenantId): bool;

    public function getVariant(int $id, int $tenantId): ProductVariant;

    public function getByProduct(int $productId, int $tenantId): array;

    public function getAllByTenant(int $tenantId): array;
}
