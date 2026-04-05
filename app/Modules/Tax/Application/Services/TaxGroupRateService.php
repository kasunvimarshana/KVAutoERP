<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Services;

use Modules\Tax\Application\Contracts\TaxGroupRateServiceInterface;
use Modules\Tax\Domain\Entities\TaxGroupRate;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRateRepositoryInterface;

class TaxGroupRateService implements TaxGroupRateServiceInterface
{
    public function __construct(
        private readonly TaxGroupRateRepositoryInterface $repository,
    ) {}

    public function addRate(int $taxGroupId, array $data): TaxGroupRate
    {
        return $this->repository->create(array_merge($data, ['tax_group_id' => $taxGroupId]));
    }

    public function removeRate(int $id): void
    {
        $this->repository->delete($id);
    }

    public function listForGroup(int $taxGroupId): array
    {
        return $this->repository->listForGroup($taxGroupId);
    }
}
