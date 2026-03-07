<?php
namespace App\Events;

use App\Models\Inventory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockLow implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Inventory $inventory,
        public readonly ?array    $productData = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('tenant.' . $this->inventory->tenant_id . '.alerts')];
    }

    public function broadcastAs(): string
    {
        return 'stock.low';
    }

    public function broadcastWith(): array
    {
        return [
            'inventory_id'       => $this->inventory->id,
            'product_id'         => $this->inventory->product_id,
            'available_quantity' => $this->inventory->available_quantity,
            'min_level'          => $this->inventory->min_level,
            'tenant_id'          => $this->inventory->tenant_id,
            'product'            => $this->productData,
        ];
    }
}
