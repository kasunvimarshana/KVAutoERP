<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\CycleCount;

interface CycleCountRepositoryInterface
{
    public function findById(int $id): ?CycleCount;

    public function findByWarehouse(int $tenantId, int $warehouseId, ?string $status): array;

    public function create(array $data): CycleCount;

    public function update(int $id, array $data): ?CycleCount;
}
