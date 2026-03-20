<?php

declare(strict_types=1);

namespace LaravelDDD\Tests\Unit\SharedKernel;

use InvalidArgumentException;
use LaravelDDD\SharedKernel\ValueObjects\Email;
use LaravelDDD\SharedKernel\ValueObjects\Money;
use LaravelDDD\SharedKernel\ValueObjects\Uuid;
use LaravelDDD\Tests\TestCase;
use UnderflowException;

/**
 */
class ValueObjectsTest extends TestCase
{
    // =========================================================================
    // Uuid
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function uuid_generate_produces_valid_format(): void
    {
        $uuid = Uuid::generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid->value(),
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function uuid_generate_produces_unique_values(): void
    {
        $uuids = array_map(fn () => Uuid::generate()->value(), range(1, 10));

        $this->assertCount(10, array_unique($uuids));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function uuid_from_string_accepts_valid_uuid(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $uuid       = Uuid::fromString($uuidString);

        $this->assertSame($uuidString, $uuid->value());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function uuid_from_string_throws_for_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Uuid::fromString('not-a-valid-uuid');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function uuid_equals_returns_true_for_same_value(): void
    {
        $a = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $b = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        $this->assertTrue($a->equals($b));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function uuid_equals_returns_false_for_different_value(): void
    {
        $a = Uuid::generate();
        $b = Uuid::generate();

        $this->assertFalse($a->equals($b));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function uuid_to_string_returns_value(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $uuid       = Uuid::fromString($uuidString);

        $this->assertSame($uuidString, (string) $uuid);
    }

    // =========================================================================
    // Email
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function email_accepts_valid_address(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('user@example.com', $email->value());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function email_throws_for_invalid_address(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('not-an-email');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function email_returns_local_part(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('user', $email->localPart());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function email_returns_domain(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('example.com', $email->domain());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function email_equals_is_case_insensitive(): void
    {
        $a = new Email('User@Example.COM');
        $b = new Email('user@example.com');

        $this->assertTrue($a->equals($b));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function email_to_string_returns_address(): void
    {
        $email = new Email('hello@world.io');

        $this->assertSame('hello@world.io', (string) $email);
    }

    // =========================================================================
    // Money
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_of_cents_stores_amount_and_currency(): void
    {
        $money = Money::ofCents(1000, 'USD');

        $this->assertSame(1000, $money->amount());
        $this->assertSame('USD', $money->currency());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_of_amount_converts_to_cents(): void
    {
        $money = Money::ofAmount(10.99, 'USD');

        $this->assertSame(1099, $money->amount());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_add_same_currency(): void
    {
        $a      = Money::ofCents(500, 'USD');
        $b      = Money::ofCents(300, 'USD');
        $result = $a->add($b);

        $this->assertSame(800, $result->amount());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_add_throws_for_different_currency(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::ofCents(500, 'USD')->add(Money::ofCents(300, 'EUR'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_subtract_same_currency(): void
    {
        $a      = Money::ofCents(500, 'USD');
        $b      = Money::ofCents(200, 'USD');
        $result = $a->subtract($b);

        $this->assertSame(300, $result->amount());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_subtract_throws_when_result_is_negative(): void
    {
        $this->expectException(UnderflowException::class);

        Money::ofCents(100, 'USD')->subtract(Money::ofCents(200, 'USD'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_multiply(): void
    {
        $money  = Money::ofCents(1000, 'USD');
        $result = $money->multiply(1.5);

        $this->assertSame(1500, $result->amount());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_is_positive(): void
    {
        $this->assertTrue(Money::ofCents(1, 'USD')->isPositive());
        $this->assertFalse(Money::ofCents(0, 'USD')->isPositive());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_is_zero(): void
    {
        $this->assertTrue(Money::ofCents(0, 'USD')->isZero());
        $this->assertFalse(Money::ofCents(1, 'USD')->isZero());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_equals(): void
    {
        $a = Money::ofCents(500, 'USD');
        $b = Money::ofCents(500, 'USD');
        $c = Money::ofCents(500, 'EUR');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_formatted(): void
    {
        $money = Money::ofCents(1099, 'USD');

        $this->assertSame('10.99 USD', $money->formatted());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function money_throws_for_empty_currency(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::ofCents(100, '');
    }
}
