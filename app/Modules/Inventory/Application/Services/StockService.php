<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\StockServiceInterface;
use Modules\Inventory\Domain\Entities\Stock;
use Modules\Inventory\Domain\Entities\StockReservation;
use Modules\Inventory\Domain\RepositoryInterfaces\StockRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockReservationRepositoryInterface;

class StockService implements StockServiceInterface
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepo,
        private readonly StockReservationRepositoryInterface $reservationRepo,
    ) {}

    public function getStock(int $productId, int $locationId, int $tenantId, ?int $variantId = null): ?Stock
    {
        return $this->stockRepo->findByProductAndLocation($productId, $variantId, $locationId, $tenantId);
    }

    public function updateStock(
        int $productId,
        int $locationId,
        float $delta,
        int $tenantId,
        string $unit = 'unit',
        ?int $variantId = null,
    ): Stock {
        $stock = $this->stockRepo->upsert([
            'tenant_id'         => $tenantId,
            'product_id'        => $productId,
            'variant_id'        => $variantId,
            'location_id'       => $locationId,
            'quantity'          => 0,
            'reserved_quantity' => 0,
            'unit'              => $unit,
        ]);

        return $this->stockRepo->updateQuantity($stock->id, $delta, $tenantId);
    }

    public function getAvailable(int $productId, int $locationId, int $tenantId, ?int $variantId = null): float
    {
        $stock = $this->getStock($productId, $locationId, $tenantId, $variantId);
        return $stock?->getAvailableQuantity() ?? 0.0;
    }

    public function reserveStock(
        int $productId,
        int $locationId,
        float $quantity,
        string $referenceType,
        int $referenceId,
        int $tenantId,
        ?int $variantId = null,
        ?\DateTimeImmutable $expiresAt = null,
    ): StockReservation {
        $available = $this->getAvailable($productId, $locationId, $tenantId, $variantId);
        if ($available < $quantity) {
            throw new \RuntimeException(
                "Insufficient available stock. Available: {$available}, Requested: {$quantity}"
            );
        }

        $reservation = $this->reservationRepo->create([
            'tenant_id'      => $tenantId,
            'product_id'     => $productId,
            'variant_id'     => $variantId,
            'location_id'    => $locationId,
            'quantity'       => $quantity,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'expires_at'     => $expiresAt?->format('Y-m-d H:i:s'),
            'status'         => 'active',
        ]);

        $stock = $this->stockRepo->findByProductAndLocation($productId, $variantId, $locationId, $tenantId);
        if ($stock !== null) {
            $this->stockRepo->updateQuantity($stock->id, 0, $tenantId);
            // Directly update reserved_quantity via upsert with new reserved value
            $this->stockRepo->upsert([
                'tenant_id'         => $tenantId,
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'location_id'       => $locationId,
                'quantity'          => $stock->quantity,
                'reserved_quantity' => $stock->reservedQuantity + $quantity,
                'unit'              => $stock->unit,
            ]);
        }

        return $reservation;
    }

    public function releaseReservation(int $reservationId, int $tenantId): bool
    {
        $reservation = $this->reservationRepo->findById($reservationId, $tenantId);
        if ($reservation === null) {
            return false;
        }

        $stock = $this->stockRepo->findByProductAndLocation(
            $reservation->productId,
            $reservation->variantId,
            $reservation->locationId,
            $tenantId,
        );

        if ($stock !== null) {
            $newReserved = max(0.0, $stock->reservedQuantity - $reservation->quantity);
            $this->stockRepo->upsert([
                'tenant_id'         => $tenantId,
                'product_id'        => $reservation->productId,
                'variant_id'        => $reservation->variantId,
                'location_id'       => $reservation->locationId,
                'quantity'          => $stock->quantity,
                'reserved_quantity' => $newReserved,
                'unit'              => $stock->unit,
            ]);
        }

        return $this->reservationRepo->delete($reservationId, $tenantId);
    }
}
