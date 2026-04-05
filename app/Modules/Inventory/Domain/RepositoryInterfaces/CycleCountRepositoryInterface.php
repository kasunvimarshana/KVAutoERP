<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\Entities\CycleCountLine;

interface CycleCountRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?CycleCount;

    public function allByTenant(int $tenantId): array;

    public function create(array $data): CycleCount;

    public function update(int $id, array $data): CycleCount;

    public function delete(int $id, int $tenantId): bool;

    public function findLines(int $cycleCountId, int $tenantId): array;

    public function createLine(array $data): CycleCountLine;

    public function updateLine(int $lineId, array $data): CycleCountLine;
}
