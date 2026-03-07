<?php

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductCreated;
use App\Services\MessageBrokerService;
use Illuminate\Support\Facades\Log;

class NotifyInventoryOnProductCreated
{
    public function __construct(private MessageBrokerService $broker) {}

    public function handle(ProductCreated $event): void
    {
        $product = $event->product;

        try {
            $this->broker->publish('inventory.product.created', [
                'product_id' => $product->id,
                'tenant_id'  => $product->tenant_id,
                'sku'        => $product->sku,
                'name'       => $product->name,
                'timestamp'  => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify inventory of product creation', [
                'product_id' => $product->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
