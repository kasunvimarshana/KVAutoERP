<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use InvalidArgumentException;

final class Price
{
    private readonly float $amount;
    private readonly string $currency;

    private const SUPPORTED_CURRENCIES = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'CNY', 'INR'];

    public function __construct(float $amount, string $currency = 'USD')
    {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);
        $this->amount = round($amount, 2);
        $this->currency = strtoupper($currency);
    }

    private function validateAmount(float $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Price amount cannot be negative.');
        }
    }

    private function validateCurrency(string $currency): void
    {
        if (!in_array(strtoupper($currency), self::SUPPORTED_CURRENCIES, true)) {
            throw new InvalidArgumentException(
                "Unsupported currency: {$currency}. Supported: " . implode(', ', self::SUPPORTED_CURRENCIES)
            );
        }
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self(max(0, $this->amount - $other->amount), $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function isGreaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot operate on prices with different currencies: {$this->currency} vs {$other->currency}"
            );
        }
    }

    public function toArray(): array
    {
        return ['amount' => $this->amount, 'currency' => $this->currency];
    }

    public function __toString(): string
    {
        return "{$this->amount} {$this->currency}";
    }
}
