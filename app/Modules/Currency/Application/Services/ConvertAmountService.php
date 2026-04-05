<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Currency\Application\Contracts\ConvertAmountServiceInterface;
use Modules\Currency\Domain\RepositoryInterfaces\ExchangeRateRepositoryInterface;

class ConvertAmountService implements ConvertAmountServiceInterface
{
    public function __construct(
        private readonly ExchangeRateRepositoryInterface $exchangeRateRepository,
    ) {}

    public function convert(int $tenantId, string $fromCurrency, string $toCurrency, float $amount): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Try direct rate
        $rate = $this->exchangeRateRepository->findRate($tenantId, $fromCurrency, $toCurrency);

        if ($rate !== null) {
            return $rate->convert($amount);
        }

        // Try inverse rate
        $inverseRate = $this->exchangeRateRepository->findRate($tenantId, $toCurrency, $fromCurrency);

        if ($inverseRate !== null && abs($inverseRate->getRate()) >= PHP_FLOAT_EPSILON) {
            return $amount / $inverseRate->getRate();
        }

        throw new NotFoundException("Exchange rate from '{$fromCurrency}' to '{$toCurrency}'");
    }
}
