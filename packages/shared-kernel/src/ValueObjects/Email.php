<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object for a validated e-mail address.
 *
 * Normalises the local part as entered and lower-cases the domain
 * portion, following RFC 5321 (local part is case-sensitive;
 * domain is case-insensitive).
 */
final class Email implements Stringable
{
    /** The local-part of the address (before the "@"). */
    private readonly string $local;

    /** The domain portion of the address (after the "@"), lower-cased. */
    private readonly string $domain;

    /**
     * @param  string  $address  The raw e-mail address string.
     *
     * @throws InvalidArgumentException When $address is not a valid e-mail.
     */
    public function __construct(private readonly string $address)
    {
        $this->validate($address);

        $atPosition = strrpos($address, '@');

        $this->local  = substr($address, 0, $atPosition);
        $this->domain = strtolower(substr($address, $atPosition + 1));
    }

    /**
     * Named constructor – create an Email from a string.
     *
     * @param  string  $address  The raw e-mail address.
     * @return self
     *
     * @throws InvalidArgumentException When $address is not a valid e-mail.
     */
    public static function fromString(string $address): self
    {
        return new self($address);
    }

    /**
     * Return the local-part of the address (before the "@").
     *
     * @return string
     */
    public function getLocal(): string
    {
        return $this->local;
    }

    /**
     * Return the domain portion of the address (lower-cased).
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Return the full canonical address: local-part preserved, domain lower-cased.
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->local . '@' . $this->domain;
    }

    /**
     * Determine structural equality with another Email.
     *
     * Comparison is case-insensitive on the domain and case-sensitive
     * on the local part, per RFC 5321.
     *
     * @param  Email  $other  The instance to compare.
     * @return bool            True when both represent the same canonical address.
     */
    public function equals(Email $other): bool
    {
        return $this->local === $other->local
            && $this->domain === $other->domain;
    }

    /**
     * Return the canonical string representation of the e-mail address.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getAddress();
    }

    /**
     * Assert that the supplied string is a syntactically valid e-mail address.
     *
     * @param  string  $address
     *
     * @throws InvalidArgumentException On validation failure.
     */
    private function validate(string $address): void
    {
        if ($address === '') {
            throw new InvalidArgumentException('Email address must not be empty.');
        }

        if (filter_var($address, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid e-mail address.', $address),
            );
        }
    }
}
