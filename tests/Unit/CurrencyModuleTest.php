<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Currency\Domain\Entities\Currency;
use Modules\Currency\Domain\Entities\ExchangeRate;
use Modules\Currency\Application\Services\ConvertAmountService;
use PHPUnit\Framework\TestCase;
class CurrencyModuleTest extends TestCase {
    public function test_currency_entity(): void {
        $c = new Currency(1, 1, 'USD', 'US Dollar', '$', 2, true, true);
        $this->assertSame('USD', $c->getCode());
        $this->assertTrue($c->isDefault());
        $this->assertSame(2, $c->getDecimalPlaces());
    }
    public function test_exchange_rate_convert(): void {
        $rate = new ExchangeRate(1, 1, 'USD', 'EUR', 0.85, new \DateTimeImmutable());
        $this->assertEqualsWithDelta(85.0, $rate->convert(100.0), 0.001);
    }
    public function test_exchange_rate_inverse(): void {
        $rate = new ExchangeRate(1, 1, 'USD', 'EUR', 0.85, new \DateTimeImmutable());
        $inv = $rate->inverse();
        $this->assertSame('EUR', $inv->getFromCurrency());
        $this->assertSame('USD', $inv->getToCurrency());
        $this->assertEqualsWithDelta(1/0.85, $inv->getRate(), 0.0001);
    }
    public function test_exchange_rate_rejects_zero_rate(): void {
        $this->expectException(\InvalidArgumentException::class);
        new ExchangeRate(null, 1, 'USD', 'EUR', 0.0, new \DateTimeImmutable());
    }
    public function test_same_currency_no_conversion(): void {
        $repo = new class implements \Modules\Currency\Domain\RepositoryInterfaces\ExchangeRateRepositoryInterface {
            public function findLatest(int $tenantId, string $from, string $to): ?\Modules\Currency\Domain\Entities\ExchangeRate { return null; }
            public function save(\Modules\Currency\Domain\Entities\ExchangeRate $rate): \Modules\Currency\Domain\Entities\ExchangeRate { return $rate; }
        };
        $svc = new ConvertAmountService($repo);
        $this->assertEqualsWithDelta(100.0, $svc->convert(1, 100.0, 'USD', 'USD'), 0.001);
    }
}
