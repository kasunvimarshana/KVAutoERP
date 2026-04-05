<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Entities;

class Order
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $orderNumber,
        private readonly string $type,   // sale|purchase|return_sale|return_purchase|transfer
        private readonly string $status, // draft|confirmed|processing|shipped|delivered|completed|cancelled|refunded
        private readonly ?int $contactId,
        private readonly ?int $warehouseId,
        private readonly string $currency,
        private readonly float $subtotal,
        private readonly float $discountAmount,
        private readonly float $taxAmount,
        private readonly float $shippingAmount,
        private readonly float $totalAmount,
        private readonly ?string $notes,
        private readonly ?array $shippingAddress,
        private readonly ?array $billingAddress,
        private readonly ?int $createdBy,
        private readonly array $metadata,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getOrderNumber(): string { return $this->orderNumber; }
    public function getType(): string { return $this->type; }
    public function getStatus(): string { return $this->status; }
    public function getContactId(): ?int { return $this->contactId; }
    public function getWarehouseId(): ?int { return $this->warehouseId; }
    public function getCurrency(): string { return $this->currency; }
    public function getSubtotal(): float { return $this->subtotal; }
    public function getDiscountAmount(): float { return $this->discountAmount; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getShippingAmount(): float { return $this->shippingAmount; }
    public function getTotalAmount(): float { return $this->totalAmount; }
    public function getNotes(): ?string { return $this->notes; }
    public function getShippingAddress(): ?array { return $this->shippingAddress; }
    public function getBillingAddress(): ?array { return $this->billingAddress; }
    public function getCreatedBy(): ?int { return $this->createdBy; }
    public function getMetadata(): array { return $this->metadata; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }

    public function getGrandTotal(): float
    {
        return $this->totalAmount;
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled', 'refunded'], true);
    }
}
