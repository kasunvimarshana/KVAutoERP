<?php

declare(strict_types=1);

namespace App\Infrastructure\Webhooks;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Webhook Dispatcher.
 *
 * Dispatches webhook notifications to tenant-configured endpoints.
 * Signs payloads with HMAC-SHA256 for security verification.
 */
class WebhookDispatcher
{
    /**
     * Dispatch a webhook event to all configured endpoints for a tenant.
     *
     * @param  string               $tenantId
     * @param  string               $event    Event name (e.g., 'inventory.created')
     * @param  array<string, mixed> $payload  Event data
     * @return void
     */
    public function dispatch(string $tenantId, string $event, array $payload): void
    {
        $endpoints = $this->getEndpointsForTenant($tenantId, $event);

        if (empty($endpoints)) {
            return;
        }

        $body = json_encode([
            'event'      => $event,
            'tenant_id'  => $tenantId,
            'payload'    => $payload,
            'timestamp'  => now()->toISOString(),
        ], JSON_THROW_ON_ERROR);

        $signature = $this->sign($body);

        foreach ($endpoints as $endpoint) {
            $this->sendWebhook($endpoint, $body, $signature, $event);
        }
    }

    /**
     * Send a webhook to a single endpoint.
     *
     * @param  string $endpoint
     * @param  string $body
     * @param  string $signature
     * @param  string $event
     * @return void
     */
    private function sendWebhook(string $endpoint, string $body, string $signature, string $event): void
    {
        try {
            Http::timeout(10)
                ->withHeaders([
                    'Content-Type'       => 'application/json',
                    'X-Webhook-Event'    => $event,
                    'X-Webhook-Signature' => "sha256={$signature}",
                    'User-Agent'         => 'IMS-Webhook/1.0',
                ])
                ->withBody($body, 'application/json')
                ->post($endpoint);
        } catch (\Throwable $e) {
            // Webhook delivery failure is non-critical - log and continue
            Log::warning('Webhook delivery failed', [
                'endpoint'  => $endpoint,
                'event'     => $event,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sign the webhook payload with HMAC-SHA256.
     *
     * @param  string $body
     * @return string
     */
    private function sign(string $body): string
    {
        return hash_hmac('sha256', $body, config('webhooks.secret', ''));
    }

    /**
     * Retrieve webhook endpoints configured for a tenant and event.
     *
     * @param  string $tenantId
     * @param  string $event
     * @return string[]
     */
    private function getEndpointsForTenant(string $tenantId, string $event): array
    {
        return \App\Domain\Webhook\Entities\WebhookEndpoint::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) use ($event): void {
                $q->whereJsonContains('events', $event)
                    ->orWhereJsonContains('events', '*');
            })
            ->pluck('url')
            ->toArray();
    }
}
