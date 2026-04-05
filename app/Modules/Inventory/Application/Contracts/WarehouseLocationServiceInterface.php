<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\WarehouseLocation;

interface WarehouseLocationServiceInterface
{
    public function getById(int $id): WarehouseLocation;

    public function getTree(int $warehouseId): Collection;

    public function getByWarehouse(int $warehouseId): Collection;

    public function create(array $data): WarehouseLocation;

    public function update(int $id, array $data): WarehouseLocation;

    public function delete(int $id): bool;
}
