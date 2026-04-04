<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;

interface WarehouseLocationRepositoryInterface
{
    public function findById(int $id): ?WarehouseLocation;

    public function findByWarehouse(int $warehouseId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function insertNode(array $data, ?int $parentId): WarehouseLocation;

    public function updateNode(int $id, array $data): WarehouseLocation;

    public function deleteNode(int $id): bool;

    public function move(int $id, ?int $newParentId): WarehouseLocation;

    public function getTree(int $warehouseId): array;

    public function getDescendants(int $id): array;
}
