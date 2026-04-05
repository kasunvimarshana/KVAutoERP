<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\RepositoryInterfaces;

use Modules\Tax\Domain\Entities\TaxGroupRate;

interface TaxGroupRateRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?TaxGroupRate;

    public function findByGroup(int $taxGroupId, int $tenantId): array;

    public function create(array $data): TaxGroupRate;

    public function delete(int $id, int $tenantId): bool;

    public function deleteByGroup(int $taxGroupId, int $tenantId): bool;
}
