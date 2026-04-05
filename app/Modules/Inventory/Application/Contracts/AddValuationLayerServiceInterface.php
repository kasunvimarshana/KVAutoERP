<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\ValuationLayer;

interface AddValuationLayerServiceInterface
{
    public function add(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $quantity,
        float $unitCost,
        string $method,
        ?int $batchId,
    ): ValuationLayer;
}
