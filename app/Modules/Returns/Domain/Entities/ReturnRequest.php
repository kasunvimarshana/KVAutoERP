<?php
declare(strict_types=1);
namespace Modules\Returns\Domain\Entities;
class ReturnRequest {
    public const TYPE_PURCHASE = 'purchase_return';
    public const TYPE_SALES = 'sales_return';
    public function __construct(
        private ?int $id, private int $tenantId, private string $returnType,
        private int $referenceId, private string $returnNumber, private string $status,
        private string $reason, private ?string $notes, private ?int $processedBy,
        private array $lines,
        private ?\DateTimeInterface $processedAt,
        private ?\DateTimeInterface $createdAt, private ?\DateTimeInterface $updatedAt,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getReturnType(): string { return $this->returnType; }
    public function getReferenceId(): int { return $this->referenceId; }
    public function getReturnNumber(): string { return $this->returnNumber; }
    public function getStatus(): string { return $this->status; }
    public function getReason(): string { return $this->reason; }
    public function getNotes(): ?string { return $this->notes; }
    public function getProcessedBy(): ?int { return $this->processedBy; }
    public function getLines(): array { return $this->lines; }
    public function getProcessedAt(): ?\DateTimeInterface { return $this->processedAt; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function approve(int $processedBy): void {
        if ($this->status !== 'pending') throw new \DomainException("Can only approve a pending return.");
        $this->status = 'approved'; $this->processedBy = $processedBy; $this->processedAt = new \DateTimeImmutable();
    }
    public function reject(int $processedBy): void {
        if ($this->status !== 'pending') throw new \DomainException("Can only reject a pending return.");
        $this->status = 'rejected'; $this->processedBy = $processedBy; $this->processedAt = new \DateTimeImmutable();
    }
    public function complete(): void {
        if ($this->status !== 'approved') throw new \DomainException("Can only complete an approved return.");
        $this->status = 'completed';
    }
}
