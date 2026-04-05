<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\InventoryAdjustment;
use Modules\Inventory\Domain\Entities\InventoryAdjustmentLine;

interface InventoryAdjustmentRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?InventoryAdjustment;

    public function findByNumber(string $adjustmentNumber, int $tenantId): ?InventoryAdjustment;

    public function allByTenant(int $tenantId): array;

    public function create(array $data): InventoryAdjustment;

    public function update(int $id, array $data): InventoryAdjustment;

    public function delete(int $id, int $tenantId): bool;

    public function findLines(int $adjustmentId, int $tenantId): array;

    public function createLine(array $data): InventoryAdjustmentLine;

    public function deleteLine(int $lineId, int $tenantId): bool;
}
