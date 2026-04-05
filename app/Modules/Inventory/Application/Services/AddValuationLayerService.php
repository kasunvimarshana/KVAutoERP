<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;

class AddValuationLayerService implements AddValuationLayerServiceInterface
{
    public function __construct(
        private readonly ValuationLayerRepositoryInterface $repository,
    ) {}

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
    ): ValuationLayer {
        return $this->repository->create([
            'tenant_id'         => $tenantId,
            'product_id'        => $productId,
            'variant_id'        => $variantId,
            'warehouse_id'      => $warehouseId,
            'location_id'       => $locationId,
            'batch_id'          => $batchId,
            'quantity'          => $quantity,
            'original_quantity' => $quantity,
            'unit_cost'         => $unitCost,
            'method'            => $method,
        ]);
    }
}
