<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Order  $order,
        public readonly string $previousStatus,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('tenant.' . $this->order->tenant_id . '.orders')];
    }

    public function broadcastAs(): string { return 'order.status_updated'; }

    public function broadcastWith(): array
    {
        return ['id' => $this->order->id, 'order_number' => $this->order->order_number, 'status' => $this->order->status, 'previous_status' => $this->previousStatus, 'tenant_id' => $this->order->tenant_id];
    }
}
