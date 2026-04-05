<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\Entities;
class Payment {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $type,    // receivable|payable
        private readonly int $partyId,    // customer or supplier id
        private readonly float $amount,
        private readonly string $currency,
        private readonly \DateTimeInterface $paymentDate,
        private readonly string $method,  // cash|bank|card|cheque
        private readonly ?string $reference,
        private readonly string $status,  // draft|confirmed|voided
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getType(): string { return $this->type; }
    public function getPartyId(): int { return $this->partyId; }
    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getPaymentDate(): \DateTimeInterface { return $this->paymentDate; }
    public function getMethod(): string { return $this->method; }
    public function getReference(): ?string { return $this->reference; }
    public function getStatus(): string { return $this->status; }
}
