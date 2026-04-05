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
        int $locationId,
        float $quantity,
        float $unitCost,
        string $method,
        \DateTimeImmutable $receivedAt,
        ?string $reference = null,
        ?int $batchLotId = null,
    ): ValuationLayer;
}
