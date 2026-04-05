<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Contracts;

interface ResolvePriceServiceInterface
{
    public function resolve(
        int $priceListId,
        int $productId,
        ?int $variantId,
        float $quantity,
        float $basePrice,
    ): float;
}
