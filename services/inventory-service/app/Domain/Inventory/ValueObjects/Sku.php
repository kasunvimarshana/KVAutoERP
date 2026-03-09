<?php

declare(strict_types=1);

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

/**
 * SKU (Stock Keeping Unit) value object.
 *
 * A validated, immutable SKU: uppercase, alphanumeric characters and hyphens only.
 */
final readonly class Sku
{
    public readonly string $value;

    /**
     * @param  string  $value  Raw SKU string; normalised to uppercase.
     *
     * @throws InvalidArgumentException When the format is invalid.
     */
    public function __construct(string $value)
    {
        $normalised = strtoupper(trim($value));

        if ($normalised === '') {
            throw new InvalidArgumentException('SKU cannot be empty.');
        }

        if (!preg_match('/^[A-Z0-9][A-Z0-9\-]{1,49}$/', $normalised)) {
            throw new InvalidArgumentException(
                "Invalid SKU format '{$normalised}'. Must be uppercase alphanumeric with optional hyphens, 2–50 characters."
            );
        }

        $this->value = $normalised;
    }

    /**
     * Check equality with another SKU.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
