<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Product $product) {}

    public function broadcastOn(): array
    {
        return [new Channel('tenant.' . $this->product->tenant_id . '.products')];
    }

    public function broadcastAs(): string
    {
        return 'product.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->product->id,
            'name'      => $this->product->name,
            'sku'       => $this->product->sku,
            'tenant_id' => $this->product->tenant_id,
        ];
    }
}
