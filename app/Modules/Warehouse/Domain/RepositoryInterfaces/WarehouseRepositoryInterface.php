<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\RepositoryInterfaces;

use Modules\Warehouse\Domain\Entities\Warehouse;

interface WarehouseRepositoryInterface
{
    public function findById(int $id): ?Warehouse;

    public function findByCode(int $tenantId, string $code): ?Warehouse;

    public function findDefault(int $tenantId): ?Warehouse;

    public function findActive(int $tenantId): array;

    public function create(array $data): Warehouse;

    public function update(int $id, array $data): ?Warehouse;

    public function delete(int $id): bool;

    public function all(int $tenantId): array;
}
