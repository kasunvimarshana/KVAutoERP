<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object for a product name.
 */
final class ProductName
{
    private const MIN_LENGTH = 1;
    private const MAX_LENGTH = 255;

    /**
     * @param  string  $value
     *
     * @throws InvalidArgumentException When the name is empty or exceeds the maximum length.
     */
    public function __construct(private readonly string $value)
    {
        $length = mb_strlen(trim($value));

        if ($length < self::MIN_LENGTH) {
            throw new InvalidArgumentException('Product name must not be empty.');
        }

        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Product name must not exceed %d characters.', self::MAX_LENGTH),
            );
        }
    }

    /**
     * Return the product name string.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Determine whether two names are equal (case-sensitive).
     *
     * @param  self  $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
