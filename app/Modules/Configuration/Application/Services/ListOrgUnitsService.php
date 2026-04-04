<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Modules\Configuration\Application\Contracts\ListOrgUnitsServiceInterface;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;

class ListOrgUnitsService implements ListOrgUnitsServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $tenantId): array
    {
        return $this->repository->findAllByTenant($tenantId);
    }
}
