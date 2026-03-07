<?php
namespace App\Events;

use App\Models\Inventory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Inventory $inventory) {}

    public function broadcastOn(): array
    {
        return [new Channel('tenant.' . $this->inventory->tenant_id . '.inventory')];
    }

    public function broadcastAs(): string
    {
        return 'inventory.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id'                 => $this->inventory->id,
            'product_id'         => $this->inventory->product_id,
            'quantity'           => $this->inventory->quantity,
            'available_quantity' => $this->inventory->available_quantity,
            'tenant_id'          => $this->inventory->tenant_id,
        ];
    }
}
