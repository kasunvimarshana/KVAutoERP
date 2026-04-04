<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Modules\Configuration\Application\Contracts\GetOrgUnitServiceInterface;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\Exceptions\OrgUnitNotFoundException;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;

class GetOrgUnitService implements GetOrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $id): OrgUnit
    {
        $orgUnit = $this->repository->findById($id);

        if ($orgUnit === null) {
            throw new OrgUnitNotFoundException($id);
        }

        return $orgUnit;
    }
}
