<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Contracts;

use Modules\Tax\Domain\Entities\TaxRate;

interface TaxRateServiceInterface
{
    public function create(array $data): TaxRate;

    public function update(int $id, array $data): TaxRate;

    public function delete(int $id, int $tenantId): bool;

    public function findById(int $id, int $tenantId): TaxRate;

    public function allByTenant(int $tenantId): array;

    public function getActive(int $tenantId): array;

    public function getByCountry(string $country, int $tenantId): array;
}
