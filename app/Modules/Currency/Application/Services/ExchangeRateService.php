<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Services;

use Modules\Currency\Application\Contracts\ExchangeRateServiceInterface;
use Modules\Currency\Domain\Entities\ExchangeRate;
use Modules\Currency\Domain\RepositoryInterfaces\ExchangeRateRepositoryInterface;

class ExchangeRateService implements ExchangeRateServiceInterface
{
    public function __construct(
        private readonly ExchangeRateRepositoryInterface $repository,
    ) {}

    public function create(array $data): ExchangeRate
    {
        return $this->repository->create($data);
    }

    public function findLatest(string $from, string $to, int $tenantId, ?\DateTimeInterface $date = null): ?ExchangeRate
    {
        return $this->repository->findLatest($from, $to, $tenantId, $date);
    }

    public function listForPair(string $from, string $to, int $tenantId): array
    {
        return $this->repository->listForPair($from, $to, $tenantId);
    }
}
