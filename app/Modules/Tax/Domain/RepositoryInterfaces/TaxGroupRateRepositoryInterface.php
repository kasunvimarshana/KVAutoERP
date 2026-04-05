<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\RepositoryInterfaces;

use Modules\Tax\Domain\Entities\TaxGroupRate;

interface TaxGroupRateRepositoryInterface
{
    /** @return TaxGroupRate[] */
    public function findByTaxGroup(int $taxGroupId): array;

    public function create(array $data): TaxGroupRate;

    public function update(int $id, array $data): ?TaxGroupRate;

    public function delete(int $id): bool;
}
