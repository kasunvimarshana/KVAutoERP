<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Currency;

interface CurrencyServiceInterface
{
    public function findById(int $id): ?Currency;

    public function findByCode(?int $tenantId, string $code): ?Currency;

    public function getDefault(?int $tenantId): ?Currency;

    /** @return Collection<int, Currency> */
    public function getActive(?int $tenantId): Collection;

    public function create(array $data): Currency;

    public function update(int $id, array $data): ?Currency;

    public function delete(int $id): bool;

    public function setDefault(?int $tenantId, int $currencyId): Currency;
}
