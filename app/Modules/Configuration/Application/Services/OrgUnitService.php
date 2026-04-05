<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Modules\Configuration\Application\Contracts\OrgUnitServiceInterface;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class OrgUnitService implements OrgUnitServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function create(array $data): OrgUnit
    {
        $parentId = $data['parent_id'] ?? null;

        if ($parentId !== null) {
            $parent = $this->repository->findById((int) $parentId);

            if ($parent === null) {
                throw new NotFoundException("Parent OrgUnit with id {$parentId} not found.");
            }

            $data['path']  = $parent->getPath() . $parent->getId() . '/';
            $data['level'] = $parent->getLevel() + 1;
        } else {
            $data['path']  = '/';
            $data['level'] = 0;
        }

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): OrgUnit
    {
        $unit = $this->repository->update($id, $data);

        if ($unit === null) {
            throw new NotFoundException("OrgUnit with id {$id} not found.");
        }

        return $unit;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getTree(int $tenantId): array
    {
        return $this->repository->getTree($tenantId);
    }

    public function getDescendants(int $id): array
    {
        return $this->repository->getDescendants($id);
    }

    public function getAncestors(int $id): array
    {
        return $this->repository->getAncestors($id);
    }

    public function move(int $id, ?int $newParentId): OrgUnit
    {
        if ($newParentId === $id) {
            throw new DomainException('An OrgUnit cannot be its own parent.');
        }

        if ($newParentId !== null) {
            $descendantIds = $this->repository->getDescendantIds($id);

            if (in_array($newParentId, $descendantIds, true)) {
                throw new DomainException('Cannot move an OrgUnit to one of its own descendants (circular reference).');
            }

            $newParent = $this->repository->findById($newParentId);

            if ($newParent === null) {
                throw new NotFoundException("Target parent OrgUnit with id {$newParentId} not found.");
            }

            $newPath  = $newParent->getPath() . $newParent->getId() . '/';
            $newLevel = $newParent->getLevel() + 1;
        } else {
            $newPath  = '/';
            $newLevel = 0;
        }

        $unit = $this->repository->update($id, [
            'parent_id' => $newParentId,
            'path'      => $newPath,
            'level'     => $newLevel,
        ]);

        if ($unit === null) {
            throw new NotFoundException("OrgUnit with id {$id} not found.");
        }

        // Propagate path/level changes to all descendants.
        $this->rebuildDescendantPaths($id);

        return $unit;
    }

    /**
     * Recursively recalculate path and level for all descendants after a move.
     */
    private function rebuildDescendantPaths(int $parentId): void
    {
        $parent = $this->repository->findById($parentId);

        if ($parent === null) {
            return;
        }

        $directChildren = array_filter(
            $this->repository->getDescendants($parentId),
            fn (OrgUnit $d) => $d->getParentId() === $parentId,
        );

        foreach ($directChildren as $child) {
            $newPath  = $parent->getPath() . $parent->getId() . '/';
            $newLevel = $parent->getLevel() + 1;

            $this->repository->update($child->getId(), [
                'path'  => $newPath,
                'level' => $newLevel,
            ]);

            $this->rebuildDescendantPaths($child->getId());
        }
    }
}
