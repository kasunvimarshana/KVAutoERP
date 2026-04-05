<?php declare(strict_types=1);
namespace Modules\Currency\Domain\RepositoryInterfaces;
use Modules\Currency\Domain\Entities\ExchangeRate;
interface ExchangeRateRepositoryInterface {
    public function findLatest(int $tenantId, string $from, string $to): ?ExchangeRate;
    public function save(ExchangeRate $rate): ExchangeRate;
}
