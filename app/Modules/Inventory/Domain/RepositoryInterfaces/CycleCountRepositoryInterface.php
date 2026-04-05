<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\Entities\CycleCountLine;

interface CycleCountRepositoryInterface
{
    public function findById(int $id): ?CycleCount;

    public function findByTenant(int $tenantId): Collection;

    public function findByWarehouse(int $warehouseId): Collection;

    public function create(array $data): CycleCount;

    public function update(int $id, array $data): ?CycleCount;

    public function addLine(int $cycleCountId, array $lineData): CycleCountLine;

    public function getLines(int $cycleCountId): Collection;
}
