<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class ValuationLayer
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly int $warehouseId,
        private readonly ?int $locationId,
        private readonly ?int $batchId,
        private readonly float $quantity,
        private readonly float $originalQuantity,
        private readonly float $unitCost,
        private readonly string $method,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getVariantId(): ?int
    {
        return $this->variantId;
    }

    public function getWarehouseId(): int
    {
        return $this->warehouseId;
    }

    public function getLocationId(): ?int
    {
        return $this->locationId;
    }

    public function getBatchId(): ?int
    {
        return $this->batchId;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getOriginalQuantity(): float
    {
        return $this->originalQuantity;
    }

    public function getUnitCost(): float
    {
        return $this->unitCost;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getRemainingQuantity(): float
    {
        return $this->quantity;
    }
}
