<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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

    public function createOrgUnit(array $data): OrgUnit
    {
        $parentId = $data['parent_id'] ?? null;
        $level = 0;
        $path = '';

        if ($parentId) {
            $parent = $this->repository->findById($parentId);
            if (! $parent) {
                throw new NotFoundException('OrgUnit (parent)', $parentId);
            }
            $level = $parent->getLevel() + 1;
        }

        // Path is built after we have the ID; use a placeholder UUID now
        $id = (string) Str::uuid();
        $path = $parentId
            ? rtrim($this->repository->findById($parentId)->getPath(), '/').'/'.$id.'/'
            : '/'.$id.'/';

        $data['id']    = $id;
        $data['level'] = $level;
        $data['path']  = $path;

        return $this->repository->create($data);
    }

    public function updateOrgUnit(string $id, array $data): OrgUnit
    {
        $this->getOrgUnit($id);

        // Parent changes must go through moveOrgUnit
        unset($data['parent_id'], $data['path'], $data['level']);

        return $this->repository->update($id, $data);
    }

    public function deleteOrgUnit(string $id): bool
    {
        $descendants = $this->repository->getDescendants($id);

        if ($descendants->isNotEmpty()) {
            throw new DomainException('Cannot delete an OrgUnit that has descendants. Move or delete them first.');
        }

        return $this->repository->delete($id);
    }

    public function getOrgUnit(string $id): OrgUnit
    {
        $unit = $this->repository->findById($id);

        if (! $unit) {
            throw new NotFoundException('OrgUnit', $id);
        }

        return $unit;
    }

    public function getTree(string $tenantId): Collection
    {
        return $this->repository->getTree($tenantId);
    }

    public function getDescendants(string $id): Collection
    {
        $this->getOrgUnit($id);

        return $this->repository->getDescendants($id);
    }

    public function getAncestors(string $id): Collection
    {
        $this->getOrgUnit($id);

        return $this->repository->getAncestors($id);
    }

    public function moveOrgUnit(string $id, ?string $newParentId): OrgUnit
    {
        $this->getOrgUnit($id);

        // Circular reference guard: new parent must not be a descendant
        if ($newParentId) {
            $descendants = $this->repository->getDescendants($id);
            $descendantIds = $descendants->map(fn (OrgUnit $u) => $u->getId())->toArray();

            if (in_array($newParentId, $descendantIds, true) || $newParentId === $id) {
                throw new DomainException('Cannot move an OrgUnit to one of its own descendants (circular reference).');
            }
        }

        return $this->repository->move($id, $newParentId);
    }
}
