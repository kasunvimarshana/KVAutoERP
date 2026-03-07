<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function broadcastOn(): array
    {
        return [new Channel('tenant.' . $this->order->tenant_id . '.orders')];
    }

    public function broadcastAs(): string { return 'order.cancelled'; }

    public function broadcastWith(): array
    {
        return ['id' => $this->order->id, 'order_number' => $this->order->order_number, 'status' => $this->order->status, 'tenant_id' => $this->order->tenant_id, 'items' => $this->order->relationLoaded('items') ? $this->order->items->map(fn ($i) => ['product_id' => $i->product_id, 'quantity' => $i->quantity])->all() : []];
    }
}
