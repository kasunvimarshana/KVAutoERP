<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\ValueObjects;

use InvalidArgumentException;
use RuntimeException;
use Stringable;

/**
 * Immutable monetary value object using BCMath for arbitrary-precision arithmetic.
 *
 * Financial rules enforced by this class:
 *   - All arithmetic uses BCMath at 10 significant decimal places internally
 *     and rounds final results to 4 decimal places with ROUND_HALF_UP.
 *   - Currency codes follow ISO 4217 (3 upper-case letters).
 *   - Cross-currency operations are prohibited and will throw.
 *   - Division by zero is prohibited and will throw.
 */
final class Money implements Stringable
{
    /** Internal BCMath working precision (extra guard digits). */
    private const WORKING_SCALE = 10;

    /** Storage and output precision (4 decimal places). */
    private const STORAGE_SCALE = 4;

    /**
     * @param  string  $amount    Decimal amount string (e.g. "19.9900").
     * @param  string  $currency  ISO 4217 currency code (e.g. "USD").
     *
     * @throws InvalidArgumentException When the amount or currency is invalid.
     */
    public function __construct(
        private readonly string $amount,
        private readonly string $currency,
    ) {
        $this->validateCurrency($currency);
        $this->validateAmount($amount);
    }

    /**
     * Named constructor – create a Money instance from a numeric value.
     *
     * @param  string|int|float  $amount    The monetary amount.
     * @param  string            $currency  ISO 4217 currency code.
     * @return self
     */
    public static function of(string|int|float $amount, string $currency): self
    {
        return new self(
            self::roundHalfUp((string) $amount, self::STORAGE_SCALE),
            strtoupper($currency),
        );
    }

    /**
     * Create a zero-valued Money for a given currency.
     *
     * @param  string  $currency  ISO 4217 currency code.
     * @return self
     */
    public static function zero(string $currency): self
    {
        return new self('0.0000', strtoupper($currency));
    }

    /**
     * Add another Money value to this one.
     *
     * @param  Money  $other  Must share the same currency.
     * @return self            New instance with the summed amount.
     *
     * @throws InvalidArgumentException On currency mismatch.
     */
    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);

        $result = bcadd($this->amount, $other->amount, self::WORKING_SCALE);

        return new self(self::roundHalfUp($result, self::STORAGE_SCALE), $this->currency);
    }

    /**
     * Subtract another Money value from this one.
     *
     * @param  Money  $other  Must share the same currency.
     * @return self            New instance with the difference.
     *
     * @throws InvalidArgumentException On currency mismatch.
     */
    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);

        $result = bcsub($this->amount, $other->amount, self::WORKING_SCALE);

        return new self(self::roundHalfUp($result, self::STORAGE_SCALE), $this->currency);
    }

    /**
     * Multiply this Money by a scalar factor.
     *
     * @param  string|int|float  $factor  Multiplicand (e.g. quantity or tax rate).
     * @return self                         New instance with the product.
     */
    public function multiply(string|int|float $factor): self
    {
        $result = bcmul($this->amount, (string) $factor, self::WORKING_SCALE);

        return new self(self::roundHalfUp($result, self::STORAGE_SCALE), $this->currency);
    }

    /**
     * Divide this Money by a scalar divisor.
     *
     * @param  string|int|float  $divisor  Must not be zero.
     * @return self                          New instance with the quotient.
     *
     * @throws InvalidArgumentException When $divisor is zero.
     */
    public function divide(string|int|float $divisor): self
    {
        $divisorStr = (string) $divisor;

        if (bccomp($divisorStr, '0', self::WORKING_SCALE) === 0) {
            throw new InvalidArgumentException('Cannot divide a monetary amount by zero.');
        }

        $result = bcdiv($this->amount, $divisorStr, self::WORKING_SCALE);

        return new self(self::roundHalfUp($result, self::STORAGE_SCALE), $this->currency);
    }

    /**
     * Return the percentage of this amount.
     *
     * @param  string|int|float  $percentage  Percentage value (e.g. 10 for 10%).
     * @return self                             New instance representing the percentage amount.
     */
    public function percentage(string|int|float $percentage): self
    {
        $rate   = bcdiv((string) $percentage, '100', self::WORKING_SCALE);
        $result = bcmul($this->amount, $rate, self::WORKING_SCALE);

        return new self(self::roundHalfUp($result, self::STORAGE_SCALE), $this->currency);
    }

    /**
     * Compare this Money to another.
     *
     * @param  Money  $other  Must share the same currency.
     * @return int             -1 if less, 0 if equal, 1 if greater.
     *
     * @throws InvalidArgumentException On currency mismatch.
     */
    public function compare(Money $other): int
    {
        $this->assertSameCurrency($other);

        return bccomp($this->amount, $other->amount, self::STORAGE_SCALE);
    }

    /**
     * Determine whether this Money equals another.
     *
     * @param  Money  $other  The instance to compare.
     * @return bool            True when both amount and currency match.
     */
    public function equals(Money $other): bool
    {
        return $this->currency === $other->currency
            && bccomp($this->amount, $other->amount, self::STORAGE_SCALE) === 0;
    }

    /**
     * Determine whether this amount is greater than another.
     *
     * @param  Money  $other  Must share the same currency.
     * @return bool
     */
    public function isGreaterThan(Money $other): bool
    {
        return $this->compare($other) === 1;
    }

    /**
     * Determine whether this amount is less than another.
     *
     * @param  Money  $other  Must share the same currency.
     * @return bool
     */
    public function isLessThan(Money $other): bool
    {
        return $this->compare($other) === -1;
    }

    /**
     * Determine whether this amount is zero.
     *
     * @return bool
     */
    public function isZero(): bool
    {
        return bccomp($this->amount, '0', self::STORAGE_SCALE) === 0;
    }

    /**
     * Determine whether this amount is positive (> 0).
     *
     * @return bool
     */
    public function isPositive(): bool
    {
        return bccomp($this->amount, '0', self::STORAGE_SCALE) === 1;
    }

    /**
     * Determine whether this amount is negative (< 0).
     *
     * @return bool
     */
    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', self::STORAGE_SCALE) === -1;
    }

    /**
     * Return the raw amount string at storage precision (4 d.p.).
     *
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * Return the ISO 4217 currency code.
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Return a human-readable formatted string.
     *
     * NOTE: This method converts the BCMath amount to a PHP float solely for
     * display purposes. Display precision may differ from storage precision.
     * Never use this output for further arithmetic — always operate on
     * {@see getAmount()} or the Money value object directly.
     *
     * @param  int  $decimals  Number of decimal places in the output (default 2).
     * @return string           e.g. "USD 19.99".
     */
    public function format(int $decimals = 2): string
    {
        return sprintf('%s %s', $this->currency, number_format((float) $this->amount, $decimals, '.', ','));
    }

    /**
     * Return the string representation (currency + amount at storage precision).
     *
     * @return string  e.g. "USD 19.9900".
     */
    public function __toString(): string
    {
        return sprintf('%s %s', $this->currency, $this->amount);
    }

    /**
     * Assert that two Money instances share the same currency.
     *
     * @param  Money  $other
     *
     * @throws InvalidArgumentException On mismatch.
     */
    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot operate on amounts with different currencies: "%s" vs "%s".',
                    $this->currency,
                    $other->currency,
                ),
            );
        }
    }

    /**
     * Validate the ISO 4217 currency code format.
     *
     * @param  string  $currency
     *
     * @throws InvalidArgumentException When not 3 upper-case ASCII letters.
     */
    private function validateCurrency(string $currency): void
    {
        if (preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid ISO 4217 currency code.', $currency),
            );
        }
    }

    /**
     * Validate that the amount string is numeric.
     *
     * @param  string  $amount
     *
     * @throws InvalidArgumentException When $amount is not numeric.
     */
    private function validateAmount(string $amount): void
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid monetary amount.', $amount),
            );
        }
    }

    /**
     * Round a BCMath decimal string using ROUND_HALF_UP.
     *
     * @param  string  $value  The decimal string to round.
     * @param  int     $scale  The number of decimal places to retain.
     * @return string           Rounded decimal string.
     */
    private static function roundHalfUp(string $value, int $scale): string
    {
        // Determine sign for correct half-up behaviour on negatives.
        $isNegative = bccomp($value, '0', self::WORKING_SCALE) === -1;

        // Use the "add 0.5 * 10^-scale then truncate" method.
        $shift   = bcpow('10', (string) -$scale, self::WORKING_SCALE);
        $half    = bcmul('0.5', $shift, self::WORKING_SCALE);
        $shifted = $isNegative
            ? bcsub($value, $half, self::WORKING_SCALE)
            : bcadd($value, $half, self::WORKING_SCALE);

        return bcadd($shifted, '0', $scale);
    }
}
