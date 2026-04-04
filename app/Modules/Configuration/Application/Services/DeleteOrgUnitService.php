<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Configuration\Application\Contracts\DeleteOrgUnitServiceInterface;
use Modules\Configuration\Domain\Events\OrgUnitDeleted;
use Modules\Configuration\Domain\Exceptions\OrgUnitNotFoundException;
use Modules\Configuration\Domain\Repositories\OrgUnitRepositoryInterface;

class DeleteOrgUnitService implements DeleteOrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $orgUnit = $this->repository->findById($id);

            if ($orgUnit === null) {
                throw new OrgUnitNotFoundException($id);
            }

            $result = $this->repository->deleteNode($id);

            if ($result) {
                Event::dispatch(new OrgUnitDeleted($id, $orgUnit->tenantId));
            }

            return $result;
        });
    }
}
