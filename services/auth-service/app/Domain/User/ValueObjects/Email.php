<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use InvalidArgumentException;

/**
 * Email value object – immutable, validated on construction.
 */
final class Email
{
    private readonly string $value;

    public function __construct(string $email)
    {
        $normalized = strtolower(trim($email));

        if (! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$email}");
        }

        if (strlen($normalized) > 255) {
            throw new InvalidArgumentException('Email address exceeds maximum length of 255 characters.');
        }

        $this->value = $normalized;
    }

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

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
