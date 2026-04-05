<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;

class AddValuationLayerService implements AddValuationLayerServiceInterface
{
    public function __construct(
        private readonly ValuationLayerRepositoryInterface $repo,
    ) {}

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
    ): ValuationLayer {
        return $this->repo->create([
            'tenant_id'         => $tenantId,
            'product_id'        => $productId,
            'variant_id'        => $variantId,
            'location_id'       => $locationId,
            'batch_lot_id'      => $batchLotId,
            'quantity'          => $quantity,
            'remaining_quantity' => $quantity,
            'unit_cost'         => $unitCost,
            'valuation_method'  => $method,
            'received_at'       => $receivedAt->format('Y-m-d H:i:s'),
            'reference'         => $reference,
        ]);
    }
}
