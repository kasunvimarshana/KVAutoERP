<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\ProductVariant;

interface ProductVariantServiceInterface
{
    public function createVariant(int $productId, array $data): ProductVariant;

    public function updateVariant(int $variantId, array $data): ProductVariant;

    public function deleteVariant(int $variantId): bool;

    /** @return ProductVariant[] */
    public function getVariants(int $productId): array;
}
