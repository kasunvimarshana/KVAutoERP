<?php

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCreated;
use App\Services\MessageBrokerService;
use Illuminate\Support\Facades\Log;

class HandleOrderCreated
{
    public function __construct(private MessageBrokerService $broker) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        try {
            $this->broker->publish('order.created', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'tenant_id'    => $order->tenant_id,
                'user_id'      => $order->user_id,
                'total'        => $order->total,
                'currency'     => $order->currency,
                'item_count'   => $order->items->count(),
                'timestamp'    => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to publish order.created event', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
