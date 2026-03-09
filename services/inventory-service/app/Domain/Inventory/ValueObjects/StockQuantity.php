<?php

declare(strict_types=1);

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

/**
 * StockQuantity value object.
 *
 * Ensures stock quantities are always non-negative integers.
 */
final readonly class StockQuantity
{
    /**
     * @param  int  $value  Non-negative stock quantity.
     *
     * @throws InvalidArgumentException When value is negative.
     */
    public function __construct(public readonly int $value)
    {
        if ($this->value < 0) {
            throw new InvalidArgumentException(
                "StockQuantity cannot be negative, got: {$this->value}"
            );
        }
    }

    /**
     * Add an amount to the current quantity.
     *
     * @throws InvalidArgumentException When amount is negative.
     */
    public function add(int $amount): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException(
                "Amount to add cannot be negative: {$amount}"
            );
        }

        return new self($this->value + $amount);
    }

    /**
     * Subtract an amount from the current quantity.
     *
     * @throws InvalidArgumentException When result would be negative.
     */
    public function subtract(int $amount): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException(
                "Amount to subtract cannot be negative: {$amount}"
            );
        }

        if ($amount > $this->value) {
            throw new InvalidArgumentException(
                "Cannot subtract {$amount} from quantity {$this->value}: result would be negative."
            );
        }

        return new self($this->value - $amount);
    }

    /**
     * Whether the quantity is zero.
     */
    public function isZero(): bool
    {
        return $this->value === 0;
    }

    /**
     * Whether the quantity is greater than or equal to the given amount.
     */
    public function canFulfill(int $amount): bool
    {
        return $this->value >= $amount;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
