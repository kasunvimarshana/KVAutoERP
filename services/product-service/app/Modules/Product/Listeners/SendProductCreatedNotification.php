<?php

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductCreated;
use App\Services\MessageBrokerService;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendProductCreatedNotification implements ShouldQueue
{
    public string $queue = 'product_events';

    public function __construct(
        private readonly MessageBrokerService $messageBroker,
        private readonly WebhookService $webhookService
    ) {}

    public function handle(ProductCreated $event): void
    {
        $product = $event->product;

        Log::info('Product created event received', ['product_id' => $product->id]);

        // Publish to message broker (RabbitMQ)
        $this->messageBroker->publish(
            exchange: 'inventory_exchange',
            routingKey: 'product.created',
            message: [
                'event'      => 'ProductCreated',
                'product_id' => $product->id,
                'sku'        => $product->sku,
                'name'       => $product->name,
                'price'      => $product->price,
                'category'   => $product->category,
                'status'     => $product->status,
                'timestamp'  => now()->toISOString(),
            ]
        );

        // Dispatch webhook
        $this->webhookService->dispatch(
            event: 'product.created',
            payload: [
                'product' => $product->toArray(),
                'timestamp' => now()->toISOString(),
            ]
        );
    }

    public function failed(ProductCreated $event, \Throwable $exception): void
    {
        Log::error('Failed to handle ProductCreated event', [
            'product_id' => $event->product->id,
            'error'      => $exception->getMessage(),
        ]);
    }
}
