<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Warehouse\Domain\Entities\Location;

interface LocationServiceInterface
{
    public function create(array $data): Location;

    public function update(int $id, array $data): Location;

    public function delete(int $id): bool;

    public function find(int $id): Location;

    public function getTree(int $warehouseId): array;

    public function getDescendants(int $locationId): array;

    public function move(int $locationId, ?int $newParentId): Location;
}
