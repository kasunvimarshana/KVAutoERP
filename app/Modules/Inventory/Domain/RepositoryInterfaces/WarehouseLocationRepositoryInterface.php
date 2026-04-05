<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\WarehouseLocation;

interface WarehouseLocationRepositoryInterface
{
    public function findById(int $id): ?WarehouseLocation;

    public function findByWarehouse(int $warehouseId): Collection;

    public function findByTenant(int $tenantId): Collection;

    public function getTree(int $warehouseId): Collection;

    public function getDescendants(int $locationId): Collection;

    public function create(array $data): WarehouseLocation;

    public function update(int $id, array $data): ?WarehouseLocation;

    public function delete(int $id): bool;
}
