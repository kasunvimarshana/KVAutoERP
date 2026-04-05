<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\StockMovement;

interface StockMovementServiceInterface
{
    public function receive(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $quantity,
        float $unitCost,
        ?string $referenceType,
        ?int $referenceId,
        ?array $batchData,
        ?int $performedBy,
    ): StockMovement;

    public function issue(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $quantity,
        ?string $referenceType,
        ?int $referenceId,
        ?int $performedBy,
    ): StockMovement;

    public function transfer(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $fromWarehouseId,
        ?int $fromLocationId,
        int $toWarehouseId,
        ?int $toLocationId,
        float $quantity,
        ?int $performedBy,
    ): array;

    public function adjust(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $newQuantity,
        string $reason,
        ?int $performedBy,
    ): StockMovement;
}
