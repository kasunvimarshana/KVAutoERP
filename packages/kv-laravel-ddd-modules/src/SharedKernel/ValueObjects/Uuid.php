<?php

declare(strict_types=1);

namespace LaravelDDD\SharedKernel\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable UUID value object (UUID version 4).
 *
 * Does not require any third-party package; uses PHP's random_int() internally.
 */
final class Uuid
{
    /**
     * @param  string  $value  A valid UUID v4 string.
     *
     * @throws InvalidArgumentException When the value is not a valid UUID.
     */
    public function __construct(private readonly string $value)
    {
        if (! self::isValid($value)) {
            throw new InvalidArgumentException("Invalid UUID: '{$value}'.");
        }
    }

    /**
     * Generate a new random UUID v4.
     *
     * @return self
     */
    public static function generate(): self
    {
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            // Version 4: top nibble is 0100
            random_int(0, 0x0fff) | 0x4000,
            // Variant: top two bits are 10
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
        );

        return new self($uuid);
    }

    /**
     * Create a Uuid from an existing UUID string.
     *
     * @param  string  $uuid
     * @return self
     *
     * @throws InvalidArgumentException When the string is not a valid UUID.
     */
    public static function fromString(string $uuid): self
    {
        return new self($uuid);
    }

    /**
     * Return the raw UUID string.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Determine whether two UUIDs are equal.
     *
     * @param  self  $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    /**
     * Return the UUID string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Check whether a string is a valid UUID (any version).
     *
     * @param  string  $value
     * @return bool
     */
    private static function isValid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value,
        );
    }
}
