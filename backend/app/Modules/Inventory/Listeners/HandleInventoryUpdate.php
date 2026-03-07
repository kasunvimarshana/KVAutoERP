<?php

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\Events\InventoryUpdated;
use App\Services\MessageBrokerService;
use Illuminate\Support\Facades\Log;

class HandleInventoryUpdate
{
    public function __construct(private MessageBrokerService $broker) {}

    public function handle(InventoryUpdated $event): void
    {
        $inventory = $event->inventory;

        try {
            $this->broker->publish('inventory.updated', [
                'inventory_id' => $inventory->id,
                'product_id'   => $inventory->product_id,
                'tenant_id'    => $inventory->tenant_id,
                'action'       => $event->action,
                'quantity'     => $inventory->quantity,
                'status'       => $inventory->status,
                'context'      => $event->context,
                'timestamp'    => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to publish inventory update', [
                'inventory_id' => $inventory->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}
