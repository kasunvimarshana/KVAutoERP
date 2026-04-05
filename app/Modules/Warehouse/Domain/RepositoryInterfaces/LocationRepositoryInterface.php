<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\RepositoryInterfaces;

use Modules\Warehouse\Domain\Entities\Location;

interface LocationRepositoryInterface
{
    public function findById(int $id): ?Location;

    public function findByCode(int $tenantId, int $warehouseId, string $code): ?Location;

    public function findByWarehouse(int $warehouseId): array;

    public function getTree(int $warehouseId): array;

    public function getDescendants(int $locationId): array;

    public function create(array $data): Location;

    public function update(int $id, array $data): ?Location;

    public function delete(int $id): bool;
}
