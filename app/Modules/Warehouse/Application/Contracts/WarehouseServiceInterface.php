<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Warehouse\Domain\Entities\Warehouse;

interface WarehouseServiceInterface
{
    public function create(array $data): Warehouse;

    public function update(int $id, array $data): Warehouse;

    public function delete(int $id): bool;

    public function find(int $id): Warehouse;

    public function setDefault(int $tenantId, int $id): Warehouse;

    public function all(int $tenantId): array;

    public function findActive(int $tenantId): array;
}
