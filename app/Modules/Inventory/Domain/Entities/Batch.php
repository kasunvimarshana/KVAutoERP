<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class Batch
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly string $batchNumber,
        private readonly ?string $lotNumber,
        private readonly ?string $serialNumber,
        private readonly ?\DateTimeInterface $expiryDate,
        private readonly ?\DateTimeInterface $manufactureDate,
        private readonly ?int $supplierId,
        private readonly float $quantity,
        private readonly float $receivedQuantity,
        private readonly string $status,
        private readonly int $warehouseId,
        private readonly ?int $locationId,
        private readonly float $costPerUnit,
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

    public function getBatchNumber(): string
    {
        return $this->batchNumber;
    }

    public function getLotNumber(): ?string
    {
        return $this->lotNumber;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiryDate;
    }

    public function getManufactureDate(): ?\DateTimeInterface
    {
        return $this->manufactureDate;
    }

    public function getSupplierId(): ?int
    {
        return $this->supplierId;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getReceivedQuantity(): float
    {
        return $this->receivedQuantity;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getWarehouseId(): int
    {
        return $this->warehouseId;
    }

    public function getLocationId(): ?int
    {
        return $this->locationId;
    }

    public function getCostPerUnit(): float
    {
        return $this->costPerUnit;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isExpired(): bool
    {
        return $this->expiryDate !== null && new \DateTimeImmutable() > $this->expiryDate;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getAvailableQuantity(): float
    {
        return $this->quantity;
    }
}
