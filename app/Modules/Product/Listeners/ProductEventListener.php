<?php

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProductEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'product-events';

    public function handleCreated(ProductCreated $event): void
    {
        $this->publish('product.created', $event->product->toArray());
    }

    public function handleUpdated(ProductUpdated $event): void
    {
        $this->publish('product.updated', $event->product->toArray());
    }

    public function handleDeleted(ProductDeleted $event): void
    {
        $this->publish('product.deleted', $event->product->toArray());
    }

    private function publish(string $eventType, array $payload): void
    {
        $message = [
            'event'      => $eventType,
            'service'    => 'product-service',
            'payload'    => $payload,
            'timestamp'  => now()->toIso8601String(),
        ];

        Log::info('Publishing domain event', $message);
    }
}
