<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\Warehouse;

interface WarehouseRepositoryInterface
{
    public function findById(int $id): ?Warehouse;

    public function findByCode(int $tenantId, string $code): ?Warehouse;

    public function findByTenant(int $tenantId): Collection;

    public function findDefault(int $tenantId): ?Warehouse;

    public function create(array $data): Warehouse;

    public function update(int $id, array $data): ?Warehouse;

    public function delete(int $id): bool;
}
