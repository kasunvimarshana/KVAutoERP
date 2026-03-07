<?php
namespace App\Listeners;

use App\Events\StockLow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleStockLow implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';
    public int $tries = 3;

    public function handle(StockLow $event): void
    {
        $inventory = $event->inventory;

        Log::warning('Low stock alert', [
            'inventory_id'       => $inventory->id,
            'product_id'         => $inventory->product_id,
            'available_quantity' => $inventory->available_quantity,
            'min_level'          => $inventory->min_level,
            'tenant_id'          => $inventory->tenant_id,
        ]);

        // In production: send notification to relevant managers
        // Notification::send(User::tenant($inventory->tenant_id)->managers()->get(), new LowStockNotification($inventory, $event->productData));
    }

    public function failed(StockLow $event, \Throwable $exception): void
    {
        Log::error('HandleStockLow failed', [
            'inventory_id' => $event->inventory->id,
            'error'        => $exception->getMessage(),
        ]);
    }
}
