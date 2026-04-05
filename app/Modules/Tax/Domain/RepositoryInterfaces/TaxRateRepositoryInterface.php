<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\RepositoryInterfaces;

use Modules\Tax\Domain\Entities\TaxRate;

interface TaxRateRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?TaxRate;

    public function findByCode(string $code, int $tenantId): ?TaxRate;

    public function allByTenant(int $tenantId): array;

    public function findActive(int $tenantId): array;

    public function findByCountry(string $country, int $tenantId): array;

    public function create(array $data): TaxRate;

    public function update(int $id, array $data): TaxRate;

    public function delete(int $id, int $tenantId): bool;
}
