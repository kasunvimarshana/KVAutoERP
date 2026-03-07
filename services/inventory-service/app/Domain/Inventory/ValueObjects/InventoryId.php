<?php

namespace App\Domain\Inventory\ValueObjects;

use Illuminate\Support\Str;

final class InventoryId
{
    private function __construct(private readonly string $value)
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('InventoryId cannot be empty.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        return new self((string) Str::uuid());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
