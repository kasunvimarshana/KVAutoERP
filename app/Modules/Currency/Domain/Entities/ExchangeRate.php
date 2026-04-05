<?php declare(strict_types=1);
namespace Modules\Currency\Domain\Entities;
class ExchangeRate {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $fromCurrency,
        private readonly string $toCurrency,
        private readonly float $rate,
        private readonly \DateTimeInterface $effectiveDate,
    ) {
        if ($rate <= 0) throw new \InvalidArgumentException("Exchange rate must be positive");
    }
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getFromCurrency(): string { return $this->fromCurrency; }
    public function getToCurrency(): string { return $this->toCurrency; }
    public function getRate(): float { return $this->rate; }
    public function getEffectiveDate(): \DateTimeInterface { return $this->effectiveDate; }
    public function convert(float $amount): float { return $amount * $this->rate; }
    public function inverse(): self {
        return new self($this->id, $this->tenantId, $this->toCurrency, $this->fromCurrency, 1.0 / $this->rate, $this->effectiveDate);
    }
}
