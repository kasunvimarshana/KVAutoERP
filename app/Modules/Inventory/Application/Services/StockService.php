<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Illuminate\Support\Collection;
use Modules\Inventory\Application\Contracts\StockServiceInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;

final class StockService implements StockServiceInterface
{
    public function __construct(
        private readonly StockItemRepositoryInterface $stockItemRepository,
        private readonly StockMovementRepositoryInterface $movementRepository,
    ) {}

    public function getStock(int $productId, ?int $variantId, ?int $locationId): Collection
    {
        $items = $this->stockItemRepository->findByProduct(0, $productId, $variantId);

        if ($locationId !== null) {
            $items = $items->filter(fn ($item) => $item->locationId === $locationId);
        }

        return $items->values();
    }

    public function getTotalStock(int $tenantId, int $productId, ?int $variantId = null): float
    {
        $items = $this->stockItemRepository->findByProduct($tenantId, $productId, $variantId);

        return (float) $items->sum(fn ($item) => $item->quantityAvailable + $item->quantityReserved);
    }

    public function adjustStock(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $quantity,
        string $type,
        ?string $referenceType,
        ?int $referenceId,
    ): void {
        $stockItem = $this->stockItemRepository->upsertPosition([
            'tenant_id'          => $tenantId,
            'product_id'         => $productId,
            'product_variant_id' => $variantId,
            'warehouse_id'       => $warehouseId,
            'location_id'        => $locationId,
            'quantity_available' => $quantity,
            'unit_of_measure'    => 'unit',
        ]);

        $this->movementRepository->record([
            'tenant_id'          => $tenantId,
            'product_id'         => $productId,
            'product_variant_id' => $variantId,
            'to_location_id'     => $locationId,
            'quantity'           => $quantity,
            'type'               => $type,
            'reference_type'     => $referenceType,
            'reference_id'       => $referenceId,
            'cost_per_unit'      => 0,
            'moved_at'           => now()->toDateTimeString(),
        ]);
    }
}
