<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;

final class AddValuationLayerService implements AddValuationLayerServiceInterface
{
    public function __construct(
        private readonly ValuationLayerRepositoryInterface $valuationLayerRepository,
    ) {}

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
    ): ValuationLayer {
        return $this->valuationLayerRepository->addLayer([
            'tenant_id'          => $tenantId,
            'product_id'         => $productId,
            'product_variant_id' => $variantId,
            'warehouse_id'       => $warehouseId,
            'quantity_received'  => $qty,
            'quantity_remaining' => $qty,
            'cost_per_unit'      => $costPerUnit,
            'valuation_method'   => $method,
            'batch_number'       => $batchNumber,
            'lot_number'         => $lotNumber,
            'serial_number'      => $serialNumber,
            'received_at'        => now()->toDateString(),
            'expiry_date'        => $expiryDate !== null ? date('Y-m-d', strtotime($expiryDate)) : null,
            'is_exhausted'       => false,
        ]);
    }
}
