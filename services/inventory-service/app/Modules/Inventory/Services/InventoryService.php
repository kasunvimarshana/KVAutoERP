<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Events\InventoryUpdated;
use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Repositories\Interfaces\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function listInventory(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->inventoryRepository->findAll($filters, $perPage);
    }

    public function getInventory(int $id): Inventory
    {
        $inventory = $this->inventoryRepository->findById($id);

        if (!$inventory) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Inventory record with ID {$id} not found."
            );
        }

        return $inventory;
    }

    public function getInventoryByProductId(int $productId): ?Inventory
    {
        return $this->inventoryRepository->findByProductId($productId);
    }

    public function createInventory(InventoryDTO $dto): Inventory
    {
        return DB::transaction(function () use ($dto) {
            $existing = $this->inventoryRepository->findByProductId($dto->productId);
            if ($existing) {
                throw new \InvalidArgumentException(
                    "Inventory record for product ID {$dto->productId} already exists."
                );
            }

            $inventory = $this->inventoryRepository->create($dto);
            Log::info('Inventory created', ['inventory_id' => $inventory->id, 'product_id' => $dto->productId]);

            return $inventory;
        });
    }

    public function updateInventory(int $id, InventoryDTO $dto): Inventory
    {
        return DB::transaction(function () use ($id, $dto) {
            $inventory = $this->inventoryRepository->update($id, $dto);
            Log::info('Inventory updated', ['inventory_id' => $inventory->id]);

            Event::dispatch(new InventoryUpdated($inventory, 0, 'manual'));
            $this->checkLowStock($inventory);

            return $inventory;
        });
    }

    /**
     * Adjust inventory quantity (positive = add, negative = subtract).
     * Supports the Saga pattern compensating transaction.
     */
    public function adjustQuantity(int $productId, int $delta, string $reason = 'adjustment'): Inventory
    {
        return DB::transaction(function () use ($productId, $delta, $reason) {
            $inventory = $this->inventoryRepository->findByProductId($productId);

            if (!$inventory) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    "No inventory record found for product ID {$productId}."
                );
            }

            $previousQuantity = $inventory->quantity;

            if ($delta < 0 && $inventory->available_quantity < abs($delta)) {
                throw new \DomainException(
                    "Insufficient stock. Available: {$inventory->available_quantity}, Requested: " . abs($delta)
                );
            }

            $inventory = $this->inventoryRepository->adjustQuantity($inventory->id, $delta);

            Log::info('Inventory quantity adjusted', [
                'product_id' => $productId,
                'delta'      => $delta,
                'reason'     => $reason,
                'new_qty'    => $inventory->quantity,
            ]);

            Event::dispatch(new InventoryUpdated($inventory, $previousQuantity, $reason));
            $this->checkLowStock($inventory);

            return $inventory;
        });
    }

    /**
     * Reserve inventory for an order (part of Saga transaction).
     */
    public function reserveStock(int $productId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $quantity) {
            $result = $this->inventoryRepository->reserveQuantity($productId, $quantity);

            if (!$result) {
                throw new \DomainException(
                    "Cannot reserve {$quantity} units for product ID {$productId}: insufficient stock."
                );
            }

            Log::info('Stock reserved', ['product_id' => $productId, 'quantity' => $quantity]);
            return $result;
        });
    }

    /**
     * Release reserved inventory (compensating transaction in Saga).
     */
    public function releaseReservation(int $productId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $quantity) {
            $result = $this->inventoryRepository->releaseReservation($productId, $quantity);
            Log::info('Reservation released', ['product_id' => $productId, 'quantity' => $quantity]);
            return $result;
        });
    }

    public function deleteInventory(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $inventory = $this->inventoryRepository->findById($id);
            if (!$inventory) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    "Inventory record with ID {$id} not found."
                );
            }
            return $this->inventoryRepository->delete($id);
        });
    }

    private function checkLowStock(Inventory $inventory): void
    {
        if ($inventory->needsReorder()) {
            Event::dispatch(new LowStockAlert(
                inventoryId:       $inventory->id,
                productId:         $inventory->product_id,
                productSku:        $inventory->product_sku,
                availableQuantity: $inventory->available_quantity,
                reorderLevel:      $inventory->reorder_level
            ));
        }
    }
}
