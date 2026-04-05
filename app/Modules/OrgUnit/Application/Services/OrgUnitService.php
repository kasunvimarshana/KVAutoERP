<?php

declare(strict_types=1);

namespace Modules\OrgUnit\Application\Services;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\OrgUnit\Application\Contracts\OrgUnitServiceInterface;
use Modules\OrgUnit\Domain\Entities\OrgUnit;
use Modules\OrgUnit\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;

final class OrgUnitService implements OrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function findById(int $id): ?OrgUnit
    {
        return $this->repository->findById($id);
    }

    public function findByCode(int $tenantId, string $code): ?OrgUnit
    {
        return $this->repository->findByCode($tenantId, $code);
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->repository->findByTenant($tenantId);
    }

    public function create(array $data): OrgUnit
    {
        $parentId = $data['parent_id'] ?? null;

        if ($parentId !== null) {
            $parent = $this->repository->findById((int) $parentId);

            if ($parent === null) {
                throw new NotFoundException("Parent OrgUnit with ID {$parentId} not found.");
            }

            $data['path']  = $parent->path . $parent->id . '/';
            $data['level'] = $parent->level + 1;
        } else {
            $data['path']  = '/';
            $data['level'] = 0;
        }

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): ?OrgUnit
    {
        $unit = $this->repository->findById($id);

        if ($unit === null) {
            throw new NotFoundException("OrgUnit with ID {$id} not found.");
        }

        // parent_id changes must go through move() to handle path/level propagation
        unset($data['path'], $data['level'], $data['parent_id']);

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $unit = $this->repository->findById($id);

        if ($unit === null) {
            throw new NotFoundException("OrgUnit with ID {$id} not found.");
        }

        return $this->repository->delete($id);
    }

    public function getTree(int $tenantId): Collection
    {
        return $this->repository->getTree($tenantId);
    }

    public function getDescendants(int $orgUnitId): Collection
    {
        return $this->repository->getDescendants($orgUnitId);
    }

    public function getAncestors(int $orgUnitId): Collection
    {
        return $this->repository->getAncestors($orgUnitId);
    }

    public function move(int $orgUnitId, ?int $newParentId): OrgUnit
    {
        $unit = $this->repository->findById($orgUnitId);

        if ($unit === null) {
            throw new NotFoundException("OrgUnit with ID {$orgUnitId} not found.");
        }

        if ($newParentId !== null) {
            // Guard: cannot move a unit into one of its own descendants
            $descendants = $this->repository->getDescendants($orgUnitId);

            $isCircular = $descendants->contains(fn (OrgUnit $d) => $d->id === $newParentId);

            if ($isCircular) {
                throw new DomainException('Cannot move an OrgUnit into one of its own descendants.');
            }

            $newParent = $this->repository->findById($newParentId);

            if ($newParent === null) {
                throw new NotFoundException("Target parent OrgUnit with ID {$newParentId} not found.");
            }
        }

        return $this->repository->move($orgUnitId, $newParentId);
    }
}
