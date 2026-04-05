<?php declare(strict_types=1);
namespace Modules\Contract\Domain\Entities;

class Contract
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $code,
        private readonly string $title,
        private readonly string $type,
        private readonly int $partyId,
        private readonly string $partyType,
        private readonly \DateTimeInterface $startDate,
        private readonly ?\DateTimeInterface $endDate,
        private readonly float $value,
        private readonly string $currency,
        private readonly string $status,
        private readonly ?string $notes,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getCode(): string { return $this->code; }
    public function getTitle(): string { return $this->title; }
    public function getType(): string { return $this->type; }
    public function getPartyId(): int { return $this->partyId; }
    public function getPartyType(): string { return $this->partyType; }
    public function getStartDate(): \DateTimeInterface { return $this->startDate; }
    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
    public function getValue(): float { return $this->value; }
    public function getCurrency(): string { return $this->currency; }
    public function getStatus(): string { return $this->status; }
    public function getNotes(): ?string { return $this->notes; }

    public function isActive(): bool { return $this->status === 'active'; }

    public function isExpired(): bool
    {
        if ($this->endDate === null) {
            return false;
        }
        return $this->endDate < new \DateTimeImmutable();
    }
}
