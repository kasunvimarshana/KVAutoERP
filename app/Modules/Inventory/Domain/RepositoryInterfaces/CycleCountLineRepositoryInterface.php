<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\CycleCountLine;

interface CycleCountLineRepositoryInterface
{
    public function findByCycleCount(int $cycleCountId): array;

    public function create(array $data): CycleCountLine;

    public function update(int $id, array $data): ?CycleCountLine;

    public function bulkCreate(array $rows): void;
}
