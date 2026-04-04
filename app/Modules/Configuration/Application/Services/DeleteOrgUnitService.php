<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Configuration\Application\Contracts\DeleteOrgUnitServiceInterface;
use Modules\Configuration\Domain\Events\OrgUnitDeleted;
use Modules\Configuration\Domain\Exceptions\OrgUnitNotFoundException;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;

class DeleteOrgUnitService implements DeleteOrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $orgUnit = $this->repository->findById($id);

        if ($orgUnit === null) {
            throw new OrgUnitNotFoundException($id);
        }

        $this->repository->delete($id);

        Event::dispatch(new OrgUnitDeleted($orgUnit->tenantId, $id, $orgUnit->parentId));
    }
}
