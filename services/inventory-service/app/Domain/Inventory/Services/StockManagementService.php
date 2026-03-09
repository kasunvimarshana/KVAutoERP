<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Entities\Product;
use App\Domain\Inventory\Entities\StockMovement;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Repositories\ProductRepositoryInterface;
use RuntimeException;

/**
 * Domain service for all stock management operations.
 *
 * Contains core business logic that co-ordinates repositories and
 * enforces domain invariants around stock levels.
 */
final class StockManagementService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Record any stock movement (in, out, adjustment, etc.) for a product.
     *
     * @throws RuntimeException When the product is not found.
     */
    public function recordMovement(
        string $productId,
        int $qty,
        StockMovementType $type,
        string $reference,
        string $reason,
        string $performedBy,
    ): StockMovement {
        $product = $this->loadProduct($productId);

        return $this->productRepository->updateStock(
            productId: $productId,
            quantity: $qty,
            type: $type->value,
            reference: $reference,
            reason: $reason,
            performedBy: $performedBy,
        );
    }

    /**
     * Reserve stock for an order (decrements available qty).
     *
     * @throws RuntimeException When the product cannot be found or has insufficient stock.
     */
    public function reserve(
        string $productId,
        int $qty,
        string $orderId,
        string $tenantId,
    ): bool {
        $product = $this->loadProduct($productId);

        if (!$product->canFulfill($qty)) {
            throw new RuntimeException(
                "Insufficient stock for product {$productId}. " .
                "Requested: {$qty}, Available: {$product->getAvailableQuantity()}."
            );
        }

        return $this->productRepository->reserveStock(
            productId: $productId,
            quantity: $qty,
            orderId: $orderId,
        );
    }

    /**
     * Release previously-reserved stock (e.g., order cancellation).
     */
    public function release(
        string $productId,
        int $qty,
        string $orderId,
        string $tenantId,
    ): void {
        $this->productRepository->releaseStock(
            productId: $productId,
            quantity: $qty,
            orderId: $orderId,
        );
    }

    /**
     * Perform a stock adjustment (set to a specific absolute quantity).
     */
    public function adjust(
        string $productId,
        int $newQty,
        string $reason,
        string $performedBy,
        string $tenantId,
    ): StockMovement {
        $this->loadProduct($productId);

        return $this->productRepository->updateStock(
            productId: $productId,
            quantity: $newQty,
            type: StockMovementType::ADJUSTMENT->value,
            reference: "adjustment:{$tenantId}",
            reason: $reason,
            performedBy: $performedBy,
        );
    }

    /**
     * Scan all products in a tenant and return those with low stock.
     *
     * @return Product[]
     */
    public function checkAndAlertLowStock(string $tenantId): array
    {
        return $this->productRepository->findLowStock($tenantId);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function loadProduct(string $productId): Product
    {
        $raw = $this->productRepository->findById($productId);

        if ($raw === null) {
            throw new RuntimeException("Product not found: {$productId}");
        }

        return Product::fromArray($raw);
    }
}
