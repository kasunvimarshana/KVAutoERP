<?php

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductDeleted;
use App\Services\MessageBrokerService;
use Illuminate\Support\Facades\Log;

class NotifyInventoryOnProductDeleted
{
    public function __construct(private MessageBrokerService $broker) {}

    public function handle(ProductDeleted $event): void
    {
        $product = $event->product;

        try {
            $this->broker->publish('inventory.product.deleted', [
                'product_id' => $product->id,
                'tenant_id'  => $product->tenant_id,
                'sku'        => $product->sku,
                'timestamp'  => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify inventory of product deletion', [
                'product_id' => $product->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
