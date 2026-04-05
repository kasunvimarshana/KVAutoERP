<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\Warehouse;

interface WarehouseServiceInterface
{
    public function getById(int $id): Warehouse;

    public function getByTenant(int $tenantId): Collection;

    public function create(array $data): Warehouse;

    public function update(int $id, array $data): Warehouse;

    public function delete(int $id): bool;

    public function setDefault(int $warehouseId, int $tenantId): Warehouse;
}
