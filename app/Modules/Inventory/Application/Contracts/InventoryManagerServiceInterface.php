<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\StockMovement;

interface InventoryManagerServiceInterface
{
    public function receiveInventory(int $tenantId, array $data): StockMovement;

    public function issueInventory(int $tenantId, array $data): StockMovement;

    public function allocateAndIssue(int $tenantId, array $data): array;
}
