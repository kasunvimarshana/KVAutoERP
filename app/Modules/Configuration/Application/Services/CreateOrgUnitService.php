<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Configuration\Application\Contracts\CreateOrgUnitServiceInterface;
use Modules\Configuration\Application\DTOs\CreateOrgUnitData;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\Events\OrgUnitCreated;
use Modules\Configuration\Domain\Repositories\OrgUnitRepositoryInterface;

class CreateOrgUnitService implements CreateOrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(CreateOrgUnitData $data): OrgUnit
    {
        return DB::transaction(function () use ($data): OrgUnit {
            $orgUnit = $this->repository->insertNode([
                'tenant_id'   => $data->tenantId,
                'name'        => $data->name,
                'code'        => $data->code,
                'type'        => $data->type,
                'description' => $data->description,
                'is_active'   => $data->isActive,
                'metadata'    => $data->metadata,
                'created_by'  => $data->createdBy,
                'updated_by'  => $data->createdBy,
            ], $data->parentId);

            Event::dispatch(new OrgUnitCreated($orgUnit));

            return $orgUnit;
        });
    }
}
