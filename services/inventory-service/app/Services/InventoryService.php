<?php
namespace App\Services;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\InventoryTransactionRepositoryInterface;
use App\Infrastructure\ProductServiceClient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepositoryInterface            $inventoryRepository,
        private readonly InventoryTransactionRepositoryInterface $transactionRepository,
        private readonly ProductServiceClient                    $productClient,
    ) {}

    /**
     * List inventory records.
     * Supports cross-service filtering by product_name, product_code, category_id
     * by first resolving product IDs from the Product Service via API.
     */
    public function list(string $tenantId, array $params = []): LengthAwarePaginator|Collection
    {
        // Detect product-attribute filters
        $productFilters = array_filter([
            'product_name' => $params['product_name'] ?? null,
            'product_code' => $params['product_code'] ?? null,
            'category_id'  => $params['category_id']  ?? null,
        ]);

        $filters = ['tenant_id' => $tenantId];

        if (!empty($productFilters)) {
            // Cross-service: get matching product IDs via Product Service API
            $productIds = $this->productClient->resolveProductIds($tenantId, $productFilters);

            if (empty($productIds)) {
                return new Collection(); // No products matched
            }

            $filters['product_id'] = $productIds;
        }

        if (!empty($params['status'])) {
            $filters['status'] = $params['status'];
        }

        return $this->inventoryRepository->all($filters, $params);
    }

    public function create(string $tenantId, array $data): Inventory
    {
        $data['tenant_id']          = $tenantId;
        $data['quantity_available'] = ($data['quantity_on_hand'] ?? 0) - ($data['quantity_reserved'] ?? 0);

        $inventory = $this->inventoryRepository->create($data);

        $this->recordTransaction($inventory, 'initial', $data['quantity_on_hand'] ?? 0, 0, 'Initial stock entry');

        Log::info('Inventory created', ['id' => $inventory->id, 'tenant_id' => $tenantId]);

        return $inventory;
    }

    public function get(string $id, string $tenantId): Inventory
    {
        $inv = $this->inventoryRepository->findById($id);
        if (!$inv || $inv->tenant_id !== $tenantId) {
            throw new \RuntimeException('Inventory record not found.', 404);
        }
        return $inv;
    }

    public function update(string $id, string $tenantId, array $data): Inventory
    {
        $inv = $this->get($id, $tenantId);
        return $this->inventoryRepository->update($inv->id, $data);
    }

    public function delete(string $id, string $tenantId): void
    {
        $inv = $this->get($id, $tenantId);
        $this->inventoryRepository->delete($inv->id);
    }

    /**
     * Reserve stock — Saga Step 1 (called by Order Service).
     * Uses pessimistic locking to prevent race conditions.
     */
    public function reserveStock(string $tenantId, string $productId, int $quantity, string $orderId): Inventory
    {
        $inv = $this->inventoryRepository->findByProductId($productId, $tenantId);
        if (!$inv) throw new \RuntimeException("No inventory record for product {$productId}.", 404);

        $before = $inv->quantity_available;
        $inv    = $this->inventoryRepository->reserveStock($inv->id, $quantity);

        $this->recordTransaction($inv, 'reserve', $quantity, $before, "Reserved for order {$orderId}", 'order', $orderId);

        Log::info('Stock reserved', ['inventory_id' => $inv->id, 'quantity' => $quantity, 'order_id' => $orderId]);
        return $inv;
    }

    /**
     * Release reserved stock — Saga Compensating Action (rollback).
     * Called when order fails after stock was reserved.
     */
    public function releaseStock(string $tenantId, string $productId, int $quantity, string $orderId): Inventory
    {
        $inv = $this->inventoryRepository->findByProductId($productId, $tenantId);
        if (!$inv) throw new \RuntimeException("No inventory record for product {$productId}.", 404);

        $before = $inv->quantity_available;
        $inv    = $this->inventoryRepository->releaseStock($inv->id, $quantity);

        $this->recordTransaction($inv, 'release', $quantity, $before, "Compensating: released reservation for order {$orderId}", 'order', $orderId);

        Log::info('Stock released (rollback)', ['inventory_id' => $inv->id, 'quantity' => $quantity, 'order_id' => $orderId]);
        return $inv;
    }

    /**
     * Confirm stock deduction — Saga Step 3 (after payment confirmed).
     */
    public function confirmDeduction(string $tenantId, string $productId, int $quantity, string $orderId): Inventory
    {
        $inv    = $this->inventoryRepository->findByProductId($productId, $tenantId);
        if (!$inv) throw new \RuntimeException("No inventory record for product {$productId}.", 404);

        $before = $inv->quantity_on_hand;
        $inv    = $this->inventoryRepository->confirmDeduction($inv->id, $quantity);

        $this->recordTransaction($inv, 'deduct', $quantity, $before, "Confirmed deduction for order {$orderId}", 'order', $orderId);

        return $inv;
    }

    /**
     * Manual stock adjustment (add / remove / set).
     */
    public function adjustStock(string $id, string $tenantId, int $quantity, string $type, string $notes = ''): Inventory
    {
        $inv    = $this->get($id, $tenantId);
        $before = $inv->quantity_on_hand;
        $inv    = $this->inventoryRepository->adjustStock($inv->id, $quantity, $type);

        $this->recordTransaction($inv, 'adjustment', $quantity, $before, $notes ?: "Manual {$type} adjustment");

        return $inv;
    }

    private function recordTransaction(
        Inventory $inv,
        string    $type,
        int       $quantity,
        int       $before,
        string    $notes = '',
        string    $refType = 'manual',
        ?string   $refId   = null
    ): void {
        $this->transactionRepository->create([
            'tenant_id'      => $inv->tenant_id,
            'inventory_id'   => $inv->id,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'type'           => $type,
            'quantity'       => $quantity,
            'quantity_before'=> $before,
            'quantity_after' => $inv->quantity_on_hand,
            'notes'          => $notes,
        ]);
    }
}
