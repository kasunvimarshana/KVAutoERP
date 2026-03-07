<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Services\InventoryServiceClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleOrderCancelled implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'orders';
    public int $tries = 3;

    public function __construct(private readonly InventoryServiceClient $inventoryClient) {}

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;
        Log::info('Order cancellation compensation triggered', ['order_id' => $order->id]);

        if ($order->relationLoaded('items')) {
            foreach ($order->items as $item) {
                $this->inventoryClient->releaseStock($item->product_id, $item->quantity, $order->tenant_id, $order->id);
            }
        }
    }

    public function failed(OrderCancelled $event, \Throwable $exception): void
    {
        Log::error('HandleOrderCancelled failed', ['order_id' => $event->order->id, 'error' => $exception->getMessage()]);
    }
}
