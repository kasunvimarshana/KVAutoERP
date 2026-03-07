<?php

namespace App\Listeners;

use App\Events\ProductDeleted;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleProductDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'webhooks';
    public int $tries = 3;
    public int $backoff = 15;

    public function __construct(private readonly WebhookService $webhookService) {}

    public function handle(ProductDeleted $event): void
    {
        $inventoryServiceUrl = config('services.inventory_service_url');

        if (empty($inventoryServiceUrl)) {
            return;
        }

        $this->webhookService->sendWebhook(
            url: $inventoryServiceUrl . '/webhooks/receive',
            event: 'product.deleted',
            payload: [
                'product_id' => $event->productId,
                'tenant_id'  => $event->tenantId,
                'sku'        => $event->sku,
            ],
        );
    }

    public function failed(ProductDeleted $event, \Throwable $exception): void
    {
        Log::error('HandleProductDeleted listener failed', [
            'product_id' => $event->productId,
            'error'      => $exception->getMessage(),
        ]);
    }
}
