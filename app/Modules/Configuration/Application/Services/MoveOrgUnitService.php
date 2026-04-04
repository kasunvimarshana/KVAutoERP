<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Configuration\Application\Contracts\MoveOrgUnitServiceInterface;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\Events\OrgUnitMoved;
use Modules\Configuration\Domain\Exceptions\CircularHierarchyException;
use Modules\Configuration\Domain\Exceptions\OrgUnitNotFoundException;
use Modules\Configuration\Domain\Repositories\OrgUnitRepositoryInterface;

class MoveOrgUnitService implements MoveOrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $id, ?int $newParentId): OrgUnit
    {
        return DB::transaction(function () use ($id, $newParentId): OrgUnit {
            $orgUnit = $this->repository->findById($id);

            if ($orgUnit === null) {
                throw new OrgUnitNotFoundException($id);
            }

            if ($newParentId !== null) {
                $descendants = $this->repository->getDescendants($id);
                $descendantIds = array_column($descendants, 'id');

                if (in_array($newParentId, $descendantIds, true) || $newParentId === $id) {
                    throw new CircularHierarchyException();
                }
            }

            $oldParentId = $orgUnit->parentId;
            $moved = $this->repository->move($id, $newParentId);

            Event::dispatch(new OrgUnitMoved($id, $orgUnit->tenantId, $oldParentId, $newParentId));

            return $moved;
        });
    }
}
