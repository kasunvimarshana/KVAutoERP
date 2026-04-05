<?php declare(strict_types=1);
namespace Modules\Order\Domain\Entities;
class Order {
    public const TYPES = ['sales','purchase','transfer'];
    public const STATUSES = ['draft','confirmed','processing','shipped','delivered','cancelled','returned'];
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $orderNumber,
        private readonly string $type,       // sales|purchase|transfer
        private readonly string $status,
        private readonly int $partyId,        // customer or supplier
        private readonly ?int $warehouseId,
        private readonly \DateTimeInterface $orderDate,
        private readonly ?string $currency,
        private readonly float $subtotal,
        private readonly float $taxAmount,
        private readonly float $discountAmount,
        private readonly float $totalAmount,
        private readonly ?string $notes,
        /** @var OrderLine[] */
        private array $lines = [],
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getOrderNumber(): string { return $this->orderNumber; }
    public function getType(): string { return $this->type; }
    public function getStatus(): string { return $this->status; }
    public function getPartyId(): int { return $this->partyId; }
    public function getWarehouseId(): ?int { return $this->warehouseId; }
    public function getOrderDate(): \DateTimeInterface { return $this->orderDate; }
    public function getCurrency(): ?string { return $this->currency; }
    public function getSubtotal(): float { return $this->subtotal; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getDiscountAmount(): float { return $this->discountAmount; }
    public function getTotalAmount(): float { return $this->totalAmount; }
    public function getNotes(): ?string { return $this->notes; }
    public function getLines(): array { return $this->lines; }
    public function setLines(array $lines): void { $this->lines = $lines; }
}
