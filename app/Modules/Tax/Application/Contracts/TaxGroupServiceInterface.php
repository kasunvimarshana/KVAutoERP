<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Contracts;

use Modules\Tax\Domain\Entities\TaxGroup;
use Modules\Tax\Domain\Entities\TaxGroupRate;

interface TaxGroupServiceInterface
{
    public function create(array $data): TaxGroup;

    public function update(int $id, array $data): TaxGroup;

    public function delete(int $id, int $tenantId): bool;

    public function findById(int $id, int $tenantId): TaxGroup;

    public function allByTenant(int $tenantId): array;

    public function addRate(int $taxGroupId, int $taxRateId, int $sortOrder, int $tenantId): TaxGroupRate;

    public function removeRate(int $taxGroupRateId, int $tenantId): bool;

    public function getRates(int $taxGroupId, int $tenantId): array;
}
