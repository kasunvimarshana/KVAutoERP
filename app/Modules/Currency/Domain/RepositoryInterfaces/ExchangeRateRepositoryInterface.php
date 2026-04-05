<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\RepositoryInterfaces;

use Modules\Currency\Domain\Entities\ExchangeRate;

interface ExchangeRateRepositoryInterface
{
    public function findById(int $id): ?ExchangeRate;

    /**
     * Find the latest rate or the rate effective as-of a given date.
     */
    public function findRate(int $tenantId, string $fromCurrency, string $toCurrency, ?\DateTimeInterface $date = null): ?ExchangeRate;

    public function create(array $data): ExchangeRate;

    public function update(int $id, array $data): ?ExchangeRate;

    public function delete(int $id): bool;
}
