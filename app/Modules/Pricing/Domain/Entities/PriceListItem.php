<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Entities;

class PriceListItem
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $priceListId,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly string $priceType,
        public readonly float $price,
        public readonly float $minQuantity,
        public readonly ?float $maxQuantity,
        public readonly ?string $notes,
    ) {}
}
