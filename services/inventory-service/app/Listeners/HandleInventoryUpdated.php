<?php
namespace App\Listeners;

use App\Events\InventoryUpdated;
use App\Events\StockLow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleInventoryUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'inventory';
    public int $tries = 3;

    public function handle(InventoryUpdated $event): void
    {
        $inventory = $event->inventory;

        Log::info('Inventory updated', [
            'inventory_id' => $inventory->id,
            'product_id'   => $inventory->product_id,
            'quantity'     => $inventory->quantity,
            'tenant_id'    => $inventory->tenant_id,
        ]);

        if ($inventory->isLowStock()) {
            event(new StockLow($inventory));
        }
    }

    public function failed(InventoryUpdated $event, \Throwable $exception): void
    {
        Log::error('HandleInventoryUpdated failed', [
            'inventory_id' => $event->inventory->id,
            'error'        => $exception->getMessage(),
        ]);
    }
}
