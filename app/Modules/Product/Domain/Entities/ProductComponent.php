<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class ProductComponent
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly int $componentProductId,
        public readonly float $quantity,
        public readonly string $unit,
        public readonly ?string $notes,
    ) {}
}
