<?php

declare(strict_types=1);

namespace App\Domain\Auth\ValueObjects;

use InvalidArgumentException;

/**
 * Email Value Object.
 *
 * Immutable, self-validating representation of an e-mail address.
 */
final readonly class Email
{
    private string $value;

    /**
     * @throws InvalidArgumentException  When the supplied string is not a valid e-mail address.
     */
    public function __construct(string $email)
    {
        $normalised = mb_strtolower(trim($email));

        if (!filter_var($normalised, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid e-mail address.', $email)
            );
        }

        $this->value = $normalised;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────────────────────────────

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function getLocalPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Equality
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Value-based equality — compares the normalised address.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    // ──────────────────────────────────────────────────────────────────────
    // String representation
    // ──────────────────────────────────────────────────────────────────────

    public function __toString(): string
    {
        return $this->value;
    }
}
