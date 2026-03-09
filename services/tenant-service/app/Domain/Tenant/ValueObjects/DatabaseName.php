<?php

declare(strict_types=1);

namespace App\Domain\Tenant\ValueObjects;

use InvalidArgumentException;

/**
 * DatabaseName Value Object.
 *
 * Encapsulates and validates MySQL/MariaDB database name rules:
 *  - 1–64 characters.
 *  - Letters, digits, underscores only.
 */
final readonly class DatabaseName
{
    private string $value;

    /**
     * @param  string  $value  Raw database name string to validate.
     *
     * @throws InvalidArgumentException  When the database name format is invalid.
     */
    public function __construct(string $value)
    {
        $this->value = $this->validate($value);
    }

    /**
     * Return a new DatabaseName with the given prefix prepended.
     *
     * @param  string  $prefix  Prefix string (will be validated in combination).
     */
    public function withPrefix(string $prefix): self
    {
        return new self($prefix . $this->value);
    }

    /**
     * Return a new DatabaseName with the given suffix appended.
     *
     * @param  string  $suffix  Suffix string (will be validated in combination).
     */
    public function withSuffix(string $suffix): self
    {
        return new self($this->value . $suffix);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * @throws InvalidArgumentException
     */
    private function validate(string $value): string
    {
        if ($value === '') {
            throw new InvalidArgumentException('Database name must not be empty.');
        }

        if (strlen($value) > 64) {
            throw new InvalidArgumentException(
                'Database name must not exceed 64 characters.'
            );
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            throw new InvalidArgumentException(
                'Database name must contain only letters, digits, and underscores.'
            );
        }

        return $value;
    }
}
