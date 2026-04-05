<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\Entities;

use Modules\Core\Domain\Exceptions\DomainException;

class ExchangeRate
{
    public function __construct(
        private readonly int $id,
        private readonly int $tenantId,
        private readonly string $fromCurrency,
        private readonly string $toCurrency,
        private readonly float $rate,
        private readonly \DateTimeInterface $effectiveDate,
        private readonly string $source,
        private readonly \DateTimeInterface $createdAt,
    ) {
        if (abs($rate) < PHP_FLOAT_EPSILON) {
            throw new DomainException('Exchange rate cannot be zero.');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTenantId(): int
    {
        return $this->tenantId;
    }

    public function getFromCurrency(): string
    {
        return $this->fromCurrency;
    }

    public function getToCurrency(): string
    {
        return $this->toCurrency;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getEffectiveDate(): \DateTimeInterface
    {
        return $this->effectiveDate;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function convert(float $amount): float
    {
        return round($amount * $this->rate, 10);
    }

    public function getInverse(): self
    {
        return new self(
            id: 0,
            tenantId: $this->tenantId,
            fromCurrency: $this->toCurrency,
            toCurrency: $this->fromCurrency,
            rate: 1.0 / $this->rate,
            effectiveDate: $this->effectiveDate,
            source: $this->source,
            createdAt: $this->createdAt,
        );
    }
}
