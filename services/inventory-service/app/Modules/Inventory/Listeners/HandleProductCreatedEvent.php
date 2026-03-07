<?php

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Support\Facades\Log;

/**
 * Handles ProductCreated events from the Product Service via RabbitMQ.
 * Currently logs the event for audit purposes.
 * Extend this listener to auto-initialize inventory when a product is created.
 */
class HandleProductCreatedEvent
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    public function handle(array $event): void
    {
        $productId  = $event['product_id'] ?? null;
        $productSku = $event['sku'] ?? 'unknown';

        Log::info('ProductCreated event received in Inventory Service', [
            'product_id'  => $productId,
            'product_sku' => $productSku,
            'timestamp'   => $event['timestamp'] ?? null,
        ]);

        // Auto-initialization of inventory can be added here if desired.
        // For example:
        // if ($productId) {
        //     $this->inventoryService->createInventory(new InventoryDTO(
        //         productId:  $productId,
        //         productSku: $productSku,
        //         quantity:   0,
        //     ));
        // }
    }
}
