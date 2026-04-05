<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Currency\Application\Contracts\ConvertAmountServiceInterface;
use Modules\Currency\Domain\RepositoryInterfaces\ExchangeRateRepositoryInterface;

class ConvertAmountService implements ConvertAmountServiceInterface
{
    public function __construct(
        private readonly ExchangeRateRepositoryInterface $repository,
    ) {}

    public function convert(float $amount, string $from, string $to, int $tenantId, ?\DateTimeInterface $date = null): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = $this->repository->findLatest($from, $to, $tenantId, $date);

        if ($rate !== null) {
            return $rate->convert($amount);
        }

        $inverse = $this->repository->findLatest($to, $from, $tenantId, $date);

        if ($inverse !== null) {
            return $inverse->getInverse()->convert($amount);
        }

        throw new NotFoundException("No exchange rate found for {$from} -> {$to}.");
    }
}
