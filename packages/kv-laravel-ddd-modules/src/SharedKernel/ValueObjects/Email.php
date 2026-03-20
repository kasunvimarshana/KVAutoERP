<?php

declare(strict_types=1);

namespace LaravelDDD\SharedKernel\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable email address value object.
 */
final class Email
{
    /**
     * @param  string  $value  A valid email address string.
     *
     * @throws InvalidArgumentException When the value is not a valid email address.
     */
    public function __construct(private readonly string $value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("Invalid email address: '{$value}'.");
        }
    }

    /**
     * Return the full email address string.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Return the domain portion of the email address (after the @).
     *
     * @return string
     */
    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    /**
     * Return the local part of the email address (before the @).
     *
     * @return string
     */
    public function localPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    /**
     * Determine whether two email addresses are equal (case-insensitive).
     *
     * @param  self  $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    /**
     * Return the email address string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
