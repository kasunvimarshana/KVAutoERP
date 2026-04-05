<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class CycleCountLine
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $cycleCountId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly ?int $locationId,
        private readonly ?int $batchId,
        private readonly float $expectedQuantity,
        private readonly ?float $countedQuantity,
        private readonly ?float $variance,
        private readonly string $status,
        private readonly ?string $notes,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCycleCountId(): int
    {
        return $this->cycleCountId;
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

    public function getBatchId(): ?int
    {
        return $this->batchId;
    }

    public function getExpectedQuantity(): float
    {
        return $this->expectedQuantity;
    }

    public function getCountedQuantity(): ?float
    {
        return $this->countedQuantity;
    }

    public function getVariance(): ?float
    {
        return $this->variance;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function isVarianceSignificant(float $threshold = 0.01): bool
    {
        if ($this->variance === null) {
            return false;
        }

        return abs($this->variance) > $threshold;
    }
}
