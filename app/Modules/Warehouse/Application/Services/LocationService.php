<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Warehouse\Application\Contracts\LocationServiceInterface;
use Modules\Warehouse\Domain\Entities\Location;
use Modules\Warehouse\Domain\RepositoryInterfaces\LocationRepositoryInterface;

class LocationService implements LocationServiceInterface
{
    public function __construct(
        private readonly LocationRepositoryInterface $repository,
    ) {}

    public function create(array $data): Location
    {
        if (isset($data['parent_id']) && $data['parent_id'] !== null) {
            $parent = $this->repository->findById($data['parent_id']);

            if ($parent === null) {
                throw new NotFoundException('Location', $data['parent_id']);
            }

            $data['level'] = $parent->getLevel() + 1;
            $data['path']  = rtrim($parent->getPath(), '/') . '/' . $parent->getId();
        } else {
            $data['level'] = 0;
            $data['path']  = '/';
        }

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Location
    {
        $location = $this->repository->update($id, $data);

        if ($location === null) {
            throw new NotFoundException('Location', $id);
        }

        return $location;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Location
    {
        $location = $this->repository->findById($id);

        if ($location === null) {
            throw new NotFoundException('Location', $id);
        }

        return $location;
    }

    public function getTree(int $warehouseId): array
    {
        return $this->repository->getTree($warehouseId);
    }

    public function getDescendants(int $locationId): array
    {
        return $this->repository->getDescendants($locationId);
    }

    public function move(int $locationId, ?int $newParentId): Location
    {
        $location = $this->find($locationId);

        $level = 0;
        $path  = '/';

        if ($newParentId !== null) {
            $parent = $this->find($newParentId);
            $level  = $parent->getLevel() + 1;
            $path   = rtrim($parent->getPath(), '/') . '/' . $newParentId;
        }

        return $this->update($locationId, [
            'parent_id' => $newParentId,
            'level'     => $level,
            'path'      => $path . '/' . $location->getId(),
        ]);
    }
}
