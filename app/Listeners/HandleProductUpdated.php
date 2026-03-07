<?php

namespace App\Listeners;

use App\Events\ProductUpdated;
use App\Models\Inventory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service B Listener: Updates inventory record(s) when a product is updated in Service A.
 * Implements ShouldQueue to support asynchronous processing via a queue driver.
 */
class HandleProductUpdated implements ShouldQueue
{
    public string $queue = 'inventory';

    /**
     * Handle the event.
     * Updates the product_name in inventory to keep data consistent across services.
     *
     * @throws Throwable
     */
    public function handle(ProductUpdated $event): void
    {
        DB::transaction(function () use ($event) {
            $product = $event->product;

            $updated = Inventory::where('product_id', $product->id)
                ->update(['product_name' => $product->name]);

            Log::info('Service B: Inventory record(s) updated for product', [
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'records_updated' => $updated,
            ]);
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(ProductUpdated $event, Throwable $exception): void
    {
        Log::error('Service B: Failed to update inventory record for product', [
            'product_id' => $event->product->id,
            'error'      => $exception->getMessage(),
        ]);
    }
}
