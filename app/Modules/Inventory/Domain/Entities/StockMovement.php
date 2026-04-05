<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class StockMovement
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly int $warehouseId,
        private readonly ?int $locationId,
        private readonly string $type,
        private readonly ?string $referenceType,
        private readonly ?int $referenceId,
        private readonly float $quantity,
        private readonly string $direction,
        private readonly ?float $unitCost,
        private readonly ?int $batchId,
        private readonly ?string $lotNumber,
        private readonly ?string $serialNumber,
        private readonly ?string $notes,
        private readonly ?int $performedBy,
        private readonly \DateTimeInterface $performedAt,
        private readonly array $metadata,
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getReferenceType(): ?string
    {
        return $this->referenceType;
    }

    public function getReferenceId(): ?int
    {
        return $this->referenceId;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getUnitCost(): ?float
    {
        return $this->unitCost;
    }

    public function getBatchId(): ?int
    {
        return $this->batchId;
    }

    public function getLotNumber(): ?string
    {
        return $this->lotNumber;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getPerformedBy(): ?int
    {
        return $this->performedBy;
    }

    public function getPerformedAt(): \DateTimeInterface
    {
        return $this->performedAt;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
