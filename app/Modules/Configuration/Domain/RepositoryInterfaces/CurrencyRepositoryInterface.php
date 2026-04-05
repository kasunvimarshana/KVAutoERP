<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Currency;

interface CurrencyRepositoryInterface
{
    public function findById(int $id): ?Currency;

    public function findByCode(?int $tenantId, string $code): ?Currency;

    public function findDefault(?int $tenantId): ?Currency;

    /** @return Collection<int, Currency> */
    public function findActive(?int $tenantId): Collection;

    public function create(array $data): Currency;

    public function update(int $id, array $data): ?Currency;

    public function delete(int $id): bool;
}
