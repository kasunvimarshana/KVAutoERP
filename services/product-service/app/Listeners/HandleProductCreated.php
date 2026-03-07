<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleProductCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'webhooks';
    public int $tries = 3;
    public int $backoff = 15;

    public function __construct(private readonly WebhookService $webhookService) {}

    public function handle(ProductCreated $event): void
    {
        $product = $event->product;

        $inventoryServiceUrl = config('services.inventory_service_url');

        if (empty($inventoryServiceUrl)) {
            return;
        }

        $this->webhookService->sendWebhook(
            url: $inventoryServiceUrl . '/webhooks/receive',
            event: 'product.created',
            payload: [
                'product_id' => $product->id,
                'tenant_id'  => $product->tenant_id,
                'name'       => $product->name,
                'sku'        => $product->sku,
                'category'   => $product->category,
                'unit'       => $product->unit,
            ],
        );
    }

    public function failed(ProductCreated $event, \Throwable $exception): void
    {
        Log::error('HandleProductCreated listener failed', [
            'product_id' => $event->product->id,
            'error'      => $exception->getMessage(),
        ]);
    }
}
