<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Events\InventoryUpdated;
use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->inventoryRepository->all($filters, $perPage);
    }

    public function get(int $id): Inventory
    {
        return $this->inventoryRepository->find($id);
    }

    public function create(InventoryDTO $dto): Inventory
    {
        return DB::transaction(function () use ($dto) {
            $inventory = $this->inventoryRepository->create([
                'product_id' => $dto->productId,
                'tenant_id' => $dto->tenantId,
                'quantity' => $dto->quantity ?? 0,
                'reserved_quantity' => $dto->reservedQuantity ?? 0,
                'min_quantity' => $dto->minQuantity ?? 0,
                'max_quantity' => $dto->maxQuantity,
                'location' => $dto->location,
                'notes' => $dto->notes,
            ]);

            event(new InventoryUpdated($inventory));

            return $inventory;
        });
    }

    public function update(int $id, InventoryDTO $dto): Inventory
    {
        return DB::transaction(function () use ($id, $dto) {
            $data = array_filter([
                'quantity' => $dto->quantity,
                'reserved_quantity' => $dto->reservedQuantity,
                'min_quantity' => $dto->minQuantity,
                'max_quantity' => $dto->maxQuantity,
                'location' => $dto->location,
                'notes' => $dto->notes,
            ], fn($v) => $v !== null);

            $inventory = $this->inventoryRepository->update($id, $data);
            event(new InventoryUpdated($inventory));

            if ($inventory->quantity <= $inventory->min_quantity) {
                event(new LowStockAlert($inventory));
            }

            return $inventory;
        });
    }

    public function adjustQuantity(int $id, int $adjustment): Inventory
    {
        return DB::transaction(function () use ($id, $adjustment) {
            $inventory = $this->inventoryRepository->adjustQuantity($id, $adjustment);
            event(new InventoryUpdated($inventory));

            if ($inventory->quantity <= $inventory->min_quantity) {
                event(new LowStockAlert($inventory));
            }

            return $inventory;
        });
    }

    public function delete(int $id): bool
    {
        return $this->inventoryRepository->delete($id);
    }

    public function initializeForProduct(Product $product): Inventory
    {
        return $this->inventoryRepository->create([
            'product_id' => $product->id,
            'tenant_id' => $product->tenant_id,
            'quantity' => 0,
            'reserved_quantity' => 0,
            'min_quantity' => 0,
        ]);
    }

    public function deleteForProduct(Product $product): bool
    {
        $inventory = $this->inventoryRepository->findByProduct($product->id);
        if ($inventory) {
            return $this->inventoryRepository->delete($inventory->id);
        }
        return true;
    }
}
