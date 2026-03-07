<?php

namespace App\Domain\Inventory\ValueObjects;

final class Quantity
{
    private function __construct(private readonly int $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException(
                "Quantity cannot be negative. Got: {$value}"
            );
        }
    }

    public static function of(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    /**
     * @throws \InvalidArgumentException when result would be negative
     */
    public function subtract(self $other): self
    {
        return new self($this->value - $other->value);
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function isZero(): bool
    {
        return $this->value === 0;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
