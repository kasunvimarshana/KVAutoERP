<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Entities;

class OrderReturn
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly ?int $originalOrderId,
        private readonly string $type,   // sales_return|purchase_return
        private readonly string $status, // draft|pending_approval|approved|processing|completed|cancelled
        private readonly ?int $contactId,
        private readonly int $warehouseId,
        private readonly string $reason,
        private readonly float $restockingFee,
        private readonly ?float $creditMemoAmount,
        private readonly ?string $notes,
        private readonly bool $qualityCheck,
        private readonly array $metadata,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getOriginalOrderId(): ?int { return $this->originalOrderId; }
    public function getType(): string { return $this->type; }
    public function getStatus(): string { return $this->status; }
    public function getContactId(): ?int { return $this->contactId; }
    public function getWarehouseId(): int { return $this->warehouseId; }
    public function getReason(): string { return $this->reason; }
    public function getRestockingFee(): float { return $this->restockingFee; }
    public function getCreditMemoAmount(): ?float { return $this->creditMemoAmount; }
    public function getNotes(): ?string { return $this->notes; }
    public function isQualityCheck(): bool { return $this->qualityCheck; }
    public function getMetadata(): array { return $this->metadata; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }

    public function requiresQualityCheck(): bool
    {
        return $this->qualityCheck;
    }

    public function hasCreditMemo(): bool
    {
        return $this->creditMemoAmount !== null && $this->creditMemoAmount > 0;
    }
}
