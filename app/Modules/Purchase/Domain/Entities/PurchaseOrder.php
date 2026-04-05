<?php

declare(strict_types=1);

namespace Modules\Purchase\Domain\Entities;

class PurchaseOrder
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $contactId,
        private readonly string $referenceNo,
        private readonly \DateTimeInterface $orderDate,
        private readonly ?int $warehouseId,
        private string $status,
        private readonly string $currencyCode,
        private readonly float $exchangeRate,
        private float $subtotal,
        private float $discountAmount,
        private float $taxAmount,
        private float $total,
        private readonly ?string $notes,
        private readonly ?\DateTimeInterface $expectedDate,
        private readonly ?int $createdBy,
        private readonly ?\DateTimeInterface $createdAt,
        private readonly ?\DateTimeInterface $updatedAt,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getContactId(): int { return $this->contactId; }
    public function getReferenceNo(): string { return $this->referenceNo; }
    public function getOrderDate(): \DateTimeInterface { return $this->orderDate; }
    public function getWarehouseId(): ?int { return $this->warehouseId; }
    public function getStatus(): string { return $this->status; }
    public function getCurrencyCode(): string { return $this->currencyCode; }
    public function getExchangeRate(): float { return $this->exchangeRate; }
    public function getSubtotal(): float { return $this->subtotal; }
    public function getDiscountAmount(): float { return $this->discountAmount; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getTotal(): float { return $this->total; }
    public function getNotes(): ?string { return $this->notes; }
    public function getExpectedDate(): ?\DateTimeInterface { return $this->expectedDate; }
    public function getCreatedBy(): ?int { return $this->createdBy; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }

    public function isDraft(): bool { return $this->status === self::STATUS_DRAFT; }
    public function isCancelled(): bool { return $this->status === self::STATUS_CANCELLED; }
    public function isReceived(): bool { return $this->status === self::STATUS_RECEIVED; }

    public function setStatus(string $status): void { $this->status = $status; }
    public function setSubtotal(float $v): void { $this->subtotal = $v; }
    public function setDiscountAmount(float $v): void { $this->discountAmount = $v; }
    public function setTaxAmount(float $v): void { $this->taxAmount = $v; }
    public function setTotal(float $v): void { $this->total = $v; }
}
