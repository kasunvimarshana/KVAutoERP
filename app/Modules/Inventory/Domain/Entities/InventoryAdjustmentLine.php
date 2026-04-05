<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class InventoryAdjustmentLine
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $adjustmentId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly ?int $locationId,
        private readonly float $expectedQuantity,
        private readonly float $actualQuantity,
        private readonly float $variance,
        private readonly ?int $batchId,
        private readonly float $unitCost,
        private readonly ?string $notes,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdjustmentId(): int
    {
        return $this->adjustmentId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getVariantId(): ?int
    {
        return $this->variantId;
    }

    public function getLocationId(): ?int
    {
        return $this->locationId;
    }

    public function getExpectedQuantity(): float
    {
        return $this->expectedQuantity;
    }

    public function getActualQuantity(): float
    {
        return $this->actualQuantity;
    }

    public function getVariance(): float
    {
        return $this->actualQuantity - $this->expectedQuantity;
    }

    public function getBatchId(): ?int
    {
        return $this->batchId;
    }

    public function getUnitCost(): float
    {
        return $this->unitCost;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }
}
