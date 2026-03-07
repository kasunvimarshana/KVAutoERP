<?php

namespace App\Modules\Inventory\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $inventoryId,
        public readonly int $productId,
        public readonly string $productSku,
        public readonly int $availableQuantity,
        public readonly int $reorderLevel
    ) {}
}
