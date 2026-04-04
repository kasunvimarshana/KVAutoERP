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
use Modules\Configuration\Domain\Repositories\OrgUnitRepositoryInterface;

class UpdateOrgUnitService implements UpdateOrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateOrgUnitData $data): OrgUnit
    {
        return DB::transaction(function () use ($id, $data): OrgUnit {
            if ($this->repository->findById($id) === null) {
                throw new OrgUnitNotFoundException($id);
            }

            $payload = array_filter([
                'name'        => $data->name,
                'code'        => $data->code,
                'type'        => $data->type,
                'description' => $data->description,
                'is_active'   => $data->isActive,
                'metadata'    => $data->metadata,
                'updated_by'  => $data->updatedBy,
            ], fn ($v) => $v !== null);

            $orgUnit = $this->repository->updateNode($id, $payload);

            Event::dispatch(new OrgUnitUpdated($orgUnit));

            return $orgUnit;
        });
    }
}
