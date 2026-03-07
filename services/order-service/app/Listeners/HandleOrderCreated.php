<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleOrderCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'orders';
    public int $tries = 3;

    public function handle(OrderCreated $event): void
    {
        Log::info('Order created', ['order_id' => $event->order->id, 'order_number' => $event->order->order_number, 'tenant_id' => $event->order->tenant_id]);
    }

    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error('HandleOrderCreated failed', ['order_id' => $event->order->id, 'error' => $exception->getMessage()]);
    }
}
