<?php

declare(strict_types=1);

namespace App\Domain\Auth\ValueObjects;

use InvalidArgumentException;

/**
 * Password Value Object.
 *
 * Wraps a bcrypt-hashed password string.  The raw plaintext is never stored.
 */
final readonly class Password
{
    private string $hash;

    /**
     * Accepts a pre-hashed string.
     * Use {@see self::hash()} to create from plaintext.
     *
     * @throws InvalidArgumentException  When $hash is empty.
     */
    public function __construct(string $hash)
    {
        if (trim($hash) === '') {
            throw new InvalidArgumentException('Password hash cannot be empty.');
        }

        $this->hash = $hash;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Factory
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Hash a plaintext password with bcrypt and return a new Password instance.
     *
     * @throws InvalidArgumentException  When $plain is shorter than 8 characters.
     */
    public static function hash(string $plain): self
    {
        if (mb_strlen($plain) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }

        return new self(password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]));
    }

    /**
     * Create a Password value object from an already-hashed string (e.g. from the DB).
     */
    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Verification
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Verify a plaintext string against the stored hash.
     */
    public function verify(string $plain): bool
    {
        return password_verify($plain, $this->hash);
    }

    /**
     * Determine whether the hash needs to be re-hashed (e.g. after a cost change).
     */
    public function needsRehash(): bool
    {
        return password_needs_rehash($this->hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Accessor
    // ──────────────────────────────────────────────────────────────────────

    public function getHash(): string
    {
        return $this->hash;
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
