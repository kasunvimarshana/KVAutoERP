<?php declare(strict_types=1);
namespace Modules\Order\Domain\Entities;
class Return_ {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $originalOrderId,
        private readonly string $type,         // sales_return|purchase_return
        private readonly string $status,        // pending|approved|restocked|rejected
        private readonly string $reason,
        private readonly float $refundAmount,
        private readonly string $condition,     // good|damaged
        private readonly bool $restockItems,
        private readonly ?\DateTimeInterface $processedAt,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getOriginalOrderId(): int { return $this->originalOrderId; }
    public function getType(): string { return $this->type; }
    public function getStatus(): string { return $this->status; }
    public function getReason(): string { return $this->reason; }
    public function getRefundAmount(): float { return $this->refundAmount; }
    public function getCondition(): string { return $this->condition; }
    public function shouldRestockItems(): bool { return $this->restockItems; }
    public function getProcessedAt(): ?\DateTimeInterface { return $this->processedAt; }
}
