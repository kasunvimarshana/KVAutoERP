<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Contracts;

use Modules\Tax\Domain\Entities\TaxGroupRate;

interface TaxGroupRateServiceInterface
{
    public function addRate(int $taxGroupId, array $data): TaxGroupRate;

    public function removeRate(int $id): void;

    /** @return TaxGroupRate[] */
    public function listForGroup(int $taxGroupId): array;
}
