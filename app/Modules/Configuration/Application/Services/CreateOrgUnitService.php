<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Configuration\Application\Contracts\CreateOrgUnitServiceInterface;
use Modules\Configuration\Application\DTOs\CreateOrgUnitData;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\Events\OrgUnitCreated;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;

class CreateOrgUnitService implements CreateOrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(CreateOrgUnitData $data): OrgUnit
    {
        return DB::transaction(function () use ($data): OrgUnit {
            $orgUnit = new OrgUnit(
                id: null,
                tenantId: $data->tenantId,
                parentId: $data->parentId,
                name: $data->name,
                code: $data->code,
                type: $data->type,
                description: $data->description,
                isActive: $data->isActive,
                sortOrder: $data->sortOrder,
                children: [],
                createdAt: null,
                updatedAt: null,
            );

            $saved = $this->repository->save($orgUnit);

            Event::dispatch(new OrgUnitCreated($data->tenantId, $saved->id, $data->parentId));

            return $saved;
        });
    }
}
