<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class StockReservation
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly int $warehouseId,
        private readonly ?int $locationId,
        private readonly float $quantity,
        private readonly string $referenceType,
        private readonly int $referenceId,
        private readonly string $status,
        private readonly ?\DateTimeInterface $expiresAt,
        private readonly ?string $notes,
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

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getReferenceType(): string
    {
        return $this->referenceType;
    }

    public function getReferenceId(): int
    {
        return $this->referenceId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt !== null && new \DateTimeImmutable() > $this->expiresAt;
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true);
    }
}
