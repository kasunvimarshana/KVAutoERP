<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\RepositoryInterfaces;

use Modules\Tax\Domain\Entities\TaxGroupRate;

interface TaxGroupRateRepositoryInterface
{
    public function create(array $data): TaxGroupRate;

    public function delete(int $id): void;

    /** @return TaxGroupRate[] */
    public function listForGroup(int $taxGroupId): array;
}
