<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Domain\ValueObjects;

use LaravelDDD\SharedKernel\ValueObjects\Uuid;

/**
 * Value Object wrapping a product's UUID identifier.
 */
final class ProductId
{
    private function __construct(private readonly Uuid $uuid) {}

    /**
     * Generate a new random ProductId.
     *
     * @return self
     */
    public static function generate(): self
    {
        return new self(Uuid::generate());
    }

    /**
     * Create a ProductId from an existing UUID string.
     *
     * @param  string  $uuid
     * @return self
     */
    public static function fromString(string $uuid): self
    {
        return new self(Uuid::fromString($uuid));
    }

    /**
     * Create a ProductId from a Uuid value object.
     *
     * @param  Uuid  $uuid
     * @return self
     */
    public static function fromUuid(Uuid $uuid): self
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
        return $this->uuid->value();
    }

    /**
     * Determine whether two ProductIds are equal.
     *
     * @param  self  $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->uuid->value();
    }
}
