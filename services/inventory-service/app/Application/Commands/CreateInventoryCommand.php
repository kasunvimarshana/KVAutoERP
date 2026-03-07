<?php

namespace App\Application\Commands;

final class CreateInventoryCommand
{
    public function __construct(
        public readonly string  $tenantId,
        public readonly string  $sku,
        public readonly string  $name,
        public readonly int     $quantity,
        public readonly float   $unitCost,
        public readonly float   $unitPrice,
        public readonly ?string $description   = null,
        public readonly ?string $category      = null,
        public readonly ?string $location      = null,
        public readonly int     $minStockLevel = 0,
        public readonly int     $maxStockLevel = 9999,
        public readonly array   $metadata      = []
    ) {}
}
