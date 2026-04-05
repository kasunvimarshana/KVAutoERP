<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\ValuationLayer;

interface AddValuationLayerServiceInterface
{
    public function addLayer(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        float $qty,
        float $costPerUnit,
        string $method,
        ?string $batchNumber,
        ?string $lotNumber,
        ?string $serialNumber,
        ?string $expiryDate,
    ): ValuationLayer;
}
