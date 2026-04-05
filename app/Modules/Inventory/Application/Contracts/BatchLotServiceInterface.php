<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\BatchLot;

interface BatchLotServiceInterface
{
    public function createBatch(array $data): BatchLot;

    public function updateBatch(int $id, array $data): BatchLot;

    public function findByNumber(string $batchNumber, int $tenantId): array;

    public function findExpiring(int $days, int $tenantId): array;

    public function getActive(int $tenantId): array;

    public function quarantine(int $id, int $tenantId): BatchLot;

    public function consume(int $id, float $quantity, int $tenantId): BatchLot;
}
