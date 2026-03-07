<?php

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\Repositories\Interfaces\InventoryRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Handles ProductDeleted events from the Product Service via RabbitMQ.
 * Cleans up inventory records associated with deleted products.
 * This is a compensating transaction in the Saga pattern.
 */
class HandleProductDeletedEvent
{
    public function __construct(
        private readonly InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function handle(array $event): void
    {
        $productId = $event['product_id'] ?? null;

        if (!$productId) {
            Log::warning('ProductDeleted event missing product_id', $event);
            return;
        }

        Log::info('Handling ProductDeleted event - cleaning up inventory', ['product_id' => $productId]);

        $inventory = $this->inventoryRepository->findByProductId($productId);

        if ($inventory) {
            $this->inventoryRepository->delete($inventory->id);
            Log::info('Inventory record deleted for product', [
                'product_id'   => $productId,
                'inventory_id' => $inventory->id,
            ]);
        }
    }
}
