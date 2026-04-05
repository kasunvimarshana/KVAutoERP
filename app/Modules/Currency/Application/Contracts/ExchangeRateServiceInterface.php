<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Contracts;

use Modules\Currency\Domain\Entities\ExchangeRate;

interface ExchangeRateServiceInterface
{
    public function create(array $data): ExchangeRate;

    public function findLatest(string $from, string $to, int $tenantId, ?\DateTimeInterface $date = null): ?ExchangeRate;

    /** @return ExchangeRate[] */
    public function listForPair(string $from, string $to, int $tenantId): array;
}
