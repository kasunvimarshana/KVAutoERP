<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Configuration\Application\Contracts\UpdateOrgUnitServiceInterface;
use Modules\Configuration\Application\DTOs\UpdateOrgUnitData;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\Events\OrgUnitUpdated;
use Modules\Configuration\Domain\Exceptions\OrgUnitNotFoundException;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;

class UpdateOrgUnitService implements UpdateOrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateOrgUnitData $data): OrgUnit
    {
        return DB::transaction(function () use ($id, $data): OrgUnit {
            $orgUnit = $this->repository->findById($id);

            if ($orgUnit === null) {
                throw new OrgUnitNotFoundException($id);
            }

            if ($data->name !== null) {
                $orgUnit->name = $data->name;
            }
            if ($data->clearParentId) {
                $orgUnit->parentId = null;
            } elseif ($data->parentId !== null) {
                $orgUnit->parentId = $data->parentId;
            }
            if ($data->code !== null) {
                $orgUnit->code = $data->code;
            }
            if ($data->type !== null) {
                $orgUnit->type = $data->type;
            }
            if ($data->description !== null) {
                $orgUnit->description = $data->description;
            }
            if ($data->isActive !== null) {
                $orgUnit->isActive = $data->isActive;
            }
            if ($data->sortOrder !== null) {
                $orgUnit->sortOrder = $data->sortOrder;
            }

            $saved = $this->repository->save($orgUnit);

            Event::dispatch(new OrgUnitUpdated($saved->tenantId, $saved->id, $saved->parentId));

            return $saved;
        });
    }
}
