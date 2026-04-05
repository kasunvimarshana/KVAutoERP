<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\StockReservation;

interface ReservationServiceInterface
{
    public function reserve(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $quantity,
        string $referenceType,
        int $referenceId,
        ?\DateTimeInterface $expiresAt,
    ): StockReservation;

    public function release(int $reservationId): void;

    public function fulfill(int $reservationId): void;
}
