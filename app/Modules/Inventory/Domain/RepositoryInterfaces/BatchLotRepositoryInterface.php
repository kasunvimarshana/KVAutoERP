<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\BatchLot;

interface BatchLotRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?BatchLot;

    public function findByNumber(string $batchNumber, int $tenantId): array;

    public function findActive(int $tenantId): array;

    public function findExpiring(int $days, int $tenantId): array;

    public function findByProduct(int $productId, int $tenantId): array;

    public function create(array $data): BatchLot;

    public function update(int $id, array $data): BatchLot;

    public function delete(int $id, int $tenantId): bool;
}
