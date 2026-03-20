<?php

declare(strict_types=1);

namespace LaravelDDD\SharedKernel\ValueObjects;

use InvalidArgumentException;
use OverflowException;
use UnderflowException;

/**
 * Immutable monetary value (amount stored in smallest currency unit, e.g. cents).
 */
final class Money
{
    /**
     * @param  int     $amount    Amount in the smallest currency unit (e.g. cents for USD).
     * @param  string  $currency  ISO 4217 currency code (e.g. "USD").
     *
     * @throws InvalidArgumentException When the currency code is empty.
     */
    public function __construct(
        private readonly int $amount,
        private readonly string $currency,
    ) {
        if (trim($currency) === '') {
            throw new InvalidArgumentException('Currency code must not be empty.');
        }
    }

    /**
     * Create a Money instance from a cent amount.
     *
     * @param  int     $cents
     * @param  string  $currency
     * @return self
     */
    public static function ofCents(int $cents, string $currency): self
    {
        return new self($cents, strtoupper($currency));
    }

    /**
     * Create a Money instance from a decimal amount (converts to cents internally).
     *
     * @param  float   $amount    Decimal amount (e.g. 10.99).
     * @param  string  $currency
     * @return self
     */
    public static function ofAmount(float $amount, string $currency): self
    {
        return new self((int) round($amount * 100), strtoupper($currency));
    }

    /**
     * Return the amount in the smallest currency unit (e.g. cents).
     *
     * @return int
     */
    public function amount(): int
    {
        return $this->amount;
    }

    /**
     * Return the ISO 4217 currency code.
     *
     * @return string
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Add another Money value to this one.
     *
     * @param  self  $other
     * @return self
     *
     * @throws InvalidArgumentException When currencies differ.
     */
    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Subtract another Money value from this one.
     *
     * @param  self  $other
     * @return self
     *
     * @throws InvalidArgumentException When currencies differ.
     * @throws UnderflowException When the result would be negative.
     */
    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        $result = $this->amount - $other->amount;

        if ($result < 0) {
            throw new UnderflowException('Money subtraction result cannot be negative.');
        }

        return new self($result, $this->currency);
    }

    /**
     * Multiply the amount by a scalar multiplier.
     *
     * @param  float  $multiplier
     * @return self
     */
    public function multiply(float $multiplier): self
    {
        return new self((int) round($this->amount * $multiplier), $this->currency);
    }

    /**
     * Determine whether the amount is positive (greater than zero).
     *
     * @return bool
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Determine whether the amount is zero.
     *
     * @return bool
     */
    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    /**
     * Determine whether two Money values are equal.
     *
     * @param  self  $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && strtoupper($this->currency) === strtoupper($other->currency);
    }

    /**
     * Return a human-readable formatted string, e.g. "10.00 USD".
     *
     * @return string
     */
    public function formatted(): string
    {
        return number_format($this->amount / 100, 2).' '.$this->currency;
    }

    /**
     * Assert that two Money objects share the same currency.
     *
     * @param  self  $other
     * @return void
     *
     * @throws InvalidArgumentException When currencies differ.
     */
    private function assertSameCurrency(self $other): void
    {
        if (strtoupper($this->currency) !== strtoupper($other->currency)) {
            throw new InvalidArgumentException(
                "Currency mismatch: cannot operate on '{$this->currency}' and '{$other->currency}'.",
            );
        }
    }
}
