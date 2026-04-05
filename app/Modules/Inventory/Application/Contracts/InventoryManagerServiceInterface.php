<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

interface InventoryManagerServiceInterface
{
    public function receive(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        int $locationId,
        float $qty,
        float $costPerUnit,
        ?string $batchNumber,
        ?string $lotNumber,
        ?string $serialNumber,
        ?string $expiryDate,
    ): void;

    public function issue(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        int $locationId,
        float $qty,
        string $referenceType,
        int $referenceId,
    ): void;

    public function transfer(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        int $fromLocationId,
        int $toLocationId,
        float $qty,
    ): void;
}
