<?php

declare(strict_types=1);

namespace App\Domain\Tenant\ValueObjects;

use InvalidArgumentException;

/**
 * TenantSlug Value Object.
 *
 * Encapsulates and validates the slug format rules:
 *  - Lowercase letters, digits, and hyphens only.
 *  - Must start and end with an alphanumeric character.
 *  - Length: 2–63 characters.
 */
final readonly class TenantSlug
{
    private string $value;

    /**
     * @param  string  $value  Raw slug string to validate.
     *
     * @throws InvalidArgumentException  When the slug format is invalid.
     */
    public function __construct(string $value)
    {
        $this->value = $this->validate($value);
    }

    /**
     * Create a TenantSlug by converting an arbitrary string to a valid slug.
     *
     * Lowercases, replaces non-alphanumeric characters with hyphens, then
     * trims leading/trailing hyphens and collapses consecutive hyphens.
     */
    public static function fromName(string $name): self
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug) ?? $slug;
        $slug = preg_replace('/-+/', '-', $slug) ?? $slug;
        $slug = trim($slug, '-');

        return new self($slug);
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
            throw new InvalidArgumentException('Tenant slug must not be empty.');
        }

        if (strlen($value) < 2 || strlen($value) > 63) {
            throw new InvalidArgumentException(
                'Tenant slug must be between 2 and 63 characters long.'
            );
        }

        if (!preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $value)) {
            throw new InvalidArgumentException(
                'Tenant slug must contain only lowercase letters, digits, and hyphens, ' .
                'and must start and end with an alphanumeric character.'
            );
        }

        return $value;
    }
}
