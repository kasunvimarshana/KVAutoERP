<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\StockReservation;

interface StockReservationRepositoryInterface
{
    public function findById(int $id): ?StockReservation;

    public function findByReference(string $referenceType, int $referenceId): array;

    public function findByProduct(int $tenantId, int $productId, ?int $variantId): array;

    public function create(array $data): StockReservation;

    public function update(int $id, array $data): ?StockReservation;

    public function cancel(int $id): bool;
}
