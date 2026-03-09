<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use InvalidArgumentException;

/**
 * Password value object – validates strength on construction, hashes via bcrypt.
 */
final class Password
{
    private readonly string $hashed;

    private function __construct(string $hashed)
    {
        $this->hashed = $hashed;
    }

    /**
     * Create from a plain-text password, validating and hashing it.
     */
    public static function fromPlain(string $plain): self
    {
        self::validate($plain);

        return new self(password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]));
    }

    /**
     * Create directly from an already-hashed password (e.g. from the database).
     */
    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    /**
     * Validate plain-text password meets complexity requirements.
     *
     * @throws InvalidArgumentException
     */
    private static function validate(string $plain): void
    {
        if (strlen($plain) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }

        if (strlen($plain) > 72) {
            // bcrypt silently truncates beyond 72 bytes; reject to avoid confusion.
            throw new InvalidArgumentException('Password must not exceed 72 characters.');
        }

        if (! preg_match('/[A-Z]/', $plain)) {
            throw new InvalidArgumentException('Password must contain at least one uppercase letter.');
        }

        if (! preg_match('/[a-z]/', $plain)) {
            throw new InvalidArgumentException('Password must contain at least one lowercase letter.');
        }

        if (! preg_match('/[0-9]/', $plain)) {
            throw new InvalidArgumentException('Password must contain at least one number.');
        }

        if (! preg_match('/[^A-Za-z0-9]/', $plain)) {
            throw new InvalidArgumentException('Password must contain at least one special character.');
        }
    }

    public function getHash(): string
    {
        return $this->hashed;
    }

    public function verify(string $plain): bool
    {
        return password_verify($plain, $this->hashed);
    }

    public function __toString(): string
    {
        return $this->hashed;
    }
}
