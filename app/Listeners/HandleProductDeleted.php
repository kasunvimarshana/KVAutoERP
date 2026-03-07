<?php

namespace App\Listeners;

use App\Events\ProductDeleted;
use App\Models\Inventory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service B Listener: Removes inventory record(s) when a product is deleted in Service A.
 * Implements ShouldQueue to support asynchronous processing via a queue driver.
 */
class HandleProductDeleted implements ShouldQueue
{
    public string $queue = 'inventory';

    /**
     * Handle the event.
     * Deletes all inventory entries associated with the deleted product.
     *
     * @throws Throwable
     */
    public function handle(ProductDeleted $event): void
    {
        DB::transaction(function () use ($event) {
            $deleted = Inventory::where('product_id', $event->productId)->delete();

            Log::info('Service B: Inventory record(s) deleted for product', [
                'product_id'      => $event->productId,
                'product_name'    => $event->productName,
                'records_deleted' => $deleted,
            ]);
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(ProductDeleted $event, Throwable $exception): void
    {
        Log::error('Service B: Failed to delete inventory records for product', [
            'product_id' => $event->productId,
            'error'      => $exception->getMessage(),
        ]);
    }
}
