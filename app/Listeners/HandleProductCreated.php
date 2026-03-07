<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Models\Inventory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service B Listener: Creates an inventory record when a new product is created in Service A.
 * Implements ShouldQueue to support asynchronous processing via a queue driver.
 */
class HandleProductCreated implements ShouldQueue
{
    public string $queue = 'inventory';

    /**
     * Handle the event.
     * Creates a default inventory entry for the new product.
     *
     * @throws Throwable
     */
    public function handle(ProductCreated $event): void
    {
        DB::transaction(function () use ($event) {
            $product = $event->product;

            Inventory::create([
                'product_id'         => $product->id,
                'product_name'       => $product->name,
                'quantity'           => 0,
                'warehouse_location' => null,
                'status'             => 'out_of_stock',
            ]);

            Log::info('Service B: Inventory record created for product', [
                'product_id'   => $product->id,
                'product_name' => $product->name,
            ]);
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(ProductCreated $event, Throwable $exception): void
    {
        Log::error('Service B: Failed to create inventory record for product', [
            'product_id' => $event->product->id,
            'error'      => $exception->getMessage(),
        ]);
    }
}
