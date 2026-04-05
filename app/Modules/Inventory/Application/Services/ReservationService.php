<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Inventory\Application\Contracts\ReservationServiceInterface;
use Modules\Inventory\Domain\Entities\StockReservation;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockReservationRepositoryInterface;
use RuntimeException;

class ReservationService implements ReservationServiceInterface
{
    public function __construct(
        private readonly StockReservationRepositoryInterface $reservationRepository,
        private readonly StockItemRepositoryInterface $stockItemRepository,
    ) {}

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
    ): StockReservation {
        $stock = $this->stockItemRepository->findByProduct(
            $tenantId, $productId, $variantId, $warehouseId, $locationId,
        );

        if ($stock === null || $stock->getAvailableQuantity() < $quantity) {
            throw new RuntimeException(
                "Insufficient stock available to reserve {$quantity} units for product {$productId}.",
            );
        }

        $reservation = $this->reservationRepository->create([
            'tenant_id'      => $tenantId,
            'product_id'     => $productId,
            'variant_id'     => $variantId,
            'warehouse_id'   => $warehouseId,
            'location_id'    => $locationId,
            'quantity'       => $quantity,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'status'         => 'pending',
            'expires_at'     => $expiresAt,
        ]);

        $newReserved = $stock->getReservedQuantity() + $quantity;
        $this->stockItemRepository->updateReserved($stock->getId(), $newReserved);

        return $reservation;
    }

    public function release(int $reservationId): void
    {
        $reservation = $this->reservationRepository->findById($reservationId);

        if ($reservation === null) {
            throw new NotFoundException('StockReservation', $reservationId);
        }

        if (! $reservation->isActive()) {
            return;
        }

        $stock = $this->stockItemRepository->findByProduct(
            $reservation->getTenantId(),
            $reservation->getProductId(),
            $reservation->getVariantId(),
            $reservation->getWarehouseId(),
            $reservation->getLocationId(),
        );

        if ($stock !== null) {
            $newReserved = max(0.0, $stock->getReservedQuantity() - $reservation->getQuantity());
            $this->stockItemRepository->updateReserved($stock->getId(), $newReserved);
        }

        $this->reservationRepository->cancel($reservationId);
    }

    public function fulfill(int $reservationId): void
    {
        $reservation = $this->reservationRepository->findById($reservationId);

        if ($reservation === null) {
            throw new NotFoundException('StockReservation', $reservationId);
        }

        $stock = $this->stockItemRepository->findByProduct(
            $reservation->getTenantId(),
            $reservation->getProductId(),
            $reservation->getVariantId(),
            $reservation->getWarehouseId(),
            $reservation->getLocationId(),
        );

        if ($stock !== null) {
            $newReserved = max(0.0, $stock->getReservedQuantity() - $reservation->getQuantity());
            $this->stockItemRepository->updateReserved($stock->getId(), $newReserved);
        }

        $this->reservationRepository->update($reservationId, ['status' => 'fulfilled']);
    }
}
