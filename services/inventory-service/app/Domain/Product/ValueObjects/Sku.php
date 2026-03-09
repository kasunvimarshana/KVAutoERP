<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use InvalidArgumentException;

final class Sku
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        $this->validate($normalized);
        $this->value = $normalized;
    }

    private function validate(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException('SKU cannot be empty.');
        }

        if (strlen($value) > 100) {
            throw new InvalidArgumentException('SKU cannot exceed 100 characters.');
        }

        if (!preg_match('/^[A-Z0-9\-_\.]+$/', $value)) {
            throw new InvalidArgumentException(
                'SKU must contain only uppercase letters, digits, hyphens, underscores, and dots.'
            );
        }
    }

    public function getValue(): string
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

    public static function generate(string $prefix = 'PRD'): self
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', strtoupper($prefix)));
        $suffix = strtoupper(substr(md5(uniqid('', true)), 0, 8));
        return new self($prefix . '-' . $suffix);
    }
}
