<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\StockLocation;

interface StockLocationRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?StockLocation;

    public function findByCode(string $code, int $tenantId): ?StockLocation;

    public function allByTenant(int $tenantId): array;

    public function findByWarehouse(int $warehouseId, int $tenantId): array;

    public function getTree(int $tenantId): array;

    public function create(array $data): StockLocation;

    public function update(int $id, array $data): StockLocation;

    public function delete(int $id, int $tenantId): bool;
}
