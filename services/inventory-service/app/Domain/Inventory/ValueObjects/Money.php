<?php

declare(strict_types=1);

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

/**
 * Money value object (immutable).
 *
 * Represents a monetary amount with an ISO 4217 currency code.
 */
final readonly class Money
{
    /**
     * @param  float   $amount    Monetary amount (rounded to 4 decimal places internally).
     * @param  string  $currency  ISO 4217 currency code (e.g., "USD", "EUR").
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
    ) {
        if ($this->amount < 0) {
            throw new InvalidArgumentException(
                "Money amount cannot be negative, got: {$this->amount}"
            );
        }

        if (!preg_match('/^[A-Z]{3}$/', strtoupper($this->currency))) {
            throw new InvalidArgumentException(
                "Invalid ISO 4217 currency code: {$this->currency}"
            );
        }
    }

    /**
     * Create a Money instance from an array representation.
     *
     * @param  array{amount: float|int|string, currency: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: (float) ($data['amount'] ?? 0),
            currency: strtoupper($data['currency'] ?? 'USD'),
        );
    }

    /**
     * Add another Money value to this one.
     *
     * @throws InvalidArgumentException When currencies differ.
     */
    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(
            amount: round($this->amount + $other->amount, 4),
            currency: $this->currency,
        );
    }

    /**
     * Subtract another Money value from this one.
     *
     * @throws InvalidArgumentException When currencies differ or result is negative.
     */
    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        $result = round($this->amount - $other->amount, 4);

        if ($result < 0) {
            throw new InvalidArgumentException(
                "Subtraction results in negative money: {$result}"
            );
        }

        return new self(amount: $result, currency: $this->currency);
    }

    /**
     * Multiply the amount by a scalar factor.
     */
    public function multiply(float $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException(
                "Multiplication factor cannot be negative: {$factor}"
            );
        }

        return new self(
            amount: round($this->amount * $factor, 4),
            currency: $this->currency,
        );
    }

    /**
     * Check equality with another Money instance.
     */
    public function equals(self $other): bool
    {
        return $this->currency === $other->currency
            && abs($this->amount - $other->amount) < 0.00001;
    }

    /**
     * Return array representation.
     *
     * @return array{amount: float, currency: string}
     */
    public function toArray(): array
    {
        return [
            'amount'   => $this->amount,
            'currency' => $this->currency,
        ];
    }

    public function __toString(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Currency mismatch: {$this->currency} vs {$other->currency}"
            );
        }
    }
}
