<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\StockReservation;

interface StockReservationRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?StockReservation;

    public function findByReference(string $referenceType, int $referenceId, int $tenantId): array;

    public function findActiveByProduct(int $productId, int $locationId, int $tenantId): array;

    public function create(array $data): StockReservation;

    public function update(int $id, array $data): StockReservation;

    public function delete(int $id, int $tenantId): bool;
}
