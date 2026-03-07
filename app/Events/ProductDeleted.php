<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Service A Event: Dispatched when a product is deleted.
 * Service B listens to this event to remove the corresponding inventory records.
 */
class ProductDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param int $productId  The ID of the deleted product (model is already gone from DB)
     * @param string $productName  The name of the deleted product for cross-service lookup
     */
    public function __construct(
        public readonly int $productId,
        public readonly string $productName
    ) {}
}
