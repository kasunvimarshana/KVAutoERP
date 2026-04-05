<?php declare(strict_types=1);
namespace Modules\Currency\Application\Services;
use Modules\Currency\Application\Contracts\ConvertAmountServiceInterface;
use Modules\Currency\Domain\RepositoryInterfaces\ExchangeRateRepositoryInterface;
class ConvertAmountService implements ConvertAmountServiceInterface {
    public function __construct(private readonly ExchangeRateRepositoryInterface $repo) {}
    public function convert(int $tenantId, float $amount, string $fromCurrency, string $toCurrency): float {
        if ($fromCurrency === $toCurrency) return $amount;
        $rate = $this->repo->findLatest($tenantId, $fromCurrency, $toCurrency);
        if ($rate) return $rate->convert($amount);
        // Try inverse
        $inverse = $this->repo->findLatest($tenantId, $toCurrency, $fromCurrency);
        if ($inverse) return $inverse->inverse()->convert($amount);
        throw new \RuntimeException("No exchange rate found for {$fromCurrency} -> {$toCurrency}");
    }
}
