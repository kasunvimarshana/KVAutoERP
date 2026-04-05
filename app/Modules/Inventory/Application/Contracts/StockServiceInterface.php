<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\Stock;
use Modules\Inventory\Domain\Entities\StockReservation;

interface StockServiceInterface
{
    public function getStock(int $productId, int $locationId, int $tenantId, ?int $variantId = null): ?Stock;

    public function updateStock(
        int $productId,
        int $locationId,
        float $delta,
        int $tenantId,
        string $unit = 'unit',
        ?int $variantId = null,
    ): Stock;

    public function getAvailable(int $productId, int $locationId, int $tenantId, ?int $variantId = null): float;

    public function reserveStock(
        int $productId,
        int $locationId,
        float $quantity,
        string $referenceType,
        int $referenceId,
        int $tenantId,
        ?int $variantId = null,
        ?\DateTimeImmutable $expiresAt = null,
    ): StockReservation;

    public function releaseReservation(int $reservationId, int $tenantId): bool;
}
