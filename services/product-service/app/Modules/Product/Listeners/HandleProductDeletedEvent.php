<?php

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductDeleted;
use App\Services\MessageBrokerService;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleProductDeletedEvent implements ShouldQueue
{
    public string $queue = 'product_events';

    public function __construct(
        private readonly MessageBrokerService $messageBroker,
        private readonly WebhookService $webhookService
    ) {}

    public function handle(ProductDeleted $event): void
    {
        Log::info('Product deleted event received', ['product_id' => $event->productId]);

        // Publish to message broker so inventory-service can clean up inventory records
        $this->messageBroker->publish(
            exchange: 'inventory_exchange',
            routingKey: 'product.deleted',
            message: [
                'event'      => 'ProductDeleted',
                'product_id' => $event->productId,
                'sku'        => $event->sku,
                'name'       => $event->name,
                'timestamp'  => now()->toISOString(),
            ]
        );

        // Dispatch webhook
        $this->webhookService->dispatch(
            event: 'product.deleted',
            payload: [
                'product_id' => $event->productId,
                'sku'        => $event->sku,
                'name'       => $event->name,
                'timestamp'  => now()->toISOString(),
            ]
        );
    }

    public function failed(ProductDeleted $event, \Throwable $exception): void
    {
        Log::error('Failed to handle ProductDeleted event', [
            'product_id' => $event->productId,
            'error'      => $exception->getMessage(),
        ]);
    }
}
