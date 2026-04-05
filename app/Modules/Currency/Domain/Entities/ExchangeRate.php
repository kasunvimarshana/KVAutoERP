<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\Entities;

use InvalidArgumentException;

class ExchangeRate
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $fromCurrency,
        private readonly string $toCurrency,
        private readonly float $rate,
        private readonly \DateTimeInterface $effectiveDate,
        private readonly string $source,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTenantId(): ?int
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
        if (abs($this->rate) < PHP_FLOAT_EPSILON || $this->rate <= 0) {
            throw new InvalidArgumentException('Exchange rate must be greater than zero.');
        }

        return $amount * $this->rate;
    }
}
