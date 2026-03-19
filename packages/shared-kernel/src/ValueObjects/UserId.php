<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\ValueObjects;

use InvalidArgumentException;
use KvEnterprise\SharedKernel\Concerns\GeneratesUuid;
use Stringable;

/**
 * Value object representing an immutable User identifier (UUID v4).
 *
 * Provides the same UUID validation guarantees as TenantId while
 * remaining a distinct type so that static analysis can catch
 * accidental substitution of one identifier type for another.
 */
final class UserId implements Stringable
{
    use GeneratesUuid;

    /** Regex pattern for UUID v4 format validation. */
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * @param  string  $value  A valid UUID v4 string.
     *
     * @throws InvalidArgumentException When $value is not a valid UUID v4.
     */
    public function __construct(private readonly string $value)
    {
        $this->validate($value);
    }

    /**
     * Named constructor – create a UserId from a UUID string.
     *
     * @param  string  $uuid  A valid UUID v4 string.
     * @return self
     *
     * @throws InvalidArgumentException When $uuid is not a valid UUID v4.
     */
    public static function fromString(string $uuid): self
    {
        return new self($uuid);
    }

    /**
     * Generate a new random UserId backed by a UUID v4.
     *
     * @return self
     */
    public static function generate(): self
    {
        return new self(self::generateUuidV4());
    }

    /**
     * Return the raw UUID string value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Determine structural equality with another UserId.
     *
     * @param  UserId  $other  The instance to compare against.
     * @return bool             True when both represent the same UUID.
     */
    public function equals(UserId $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    /**
     * Return the string representation (the UUID value).
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Assert that the supplied string is a valid UUID v4.
     *
     * @param  string  $value  Value to validate.
     *
     * @throws InvalidArgumentException On format mismatch.
     */
    private function validate(string $value): void
    {
        if ($value === '') {
            throw new InvalidArgumentException('UserId must not be empty.');
        }

        if (preg_match(self::UUID_PATTERN, $value) !== 1) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid UUID v4 and cannot be used as a UserId.', $value),
            );
        }
    }
}
