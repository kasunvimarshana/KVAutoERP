<?php

namespace App\Services;

use App\Modules\Webhook\DTOs\WebhookPayloadDTO;
use App\Modules\Webhook\Models\WebhookSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WebhookService dispatches webhook notifications to registered subscribers.
 */
class WebhookService
{
    public function dispatch(string $event, array $payload): void
    {
        $subscriptions = WebhookSubscription::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->sendWebhook($subscription, $event, $payload);
        }
    }

    public function register(string $url, array $events, string $secret, ?string $description = null): WebhookSubscription
    {
        return WebhookSubscription::create([
            'url'         => $url,
            'events'      => $events,
            'secret'      => $secret, // stored as-is; use encrypted column in production
            'is_active'   => true,
            'description' => $description,
        ]);
    }

    private function sendWebhook(WebhookSubscription $subscription, string $event, array $payload): void
    {
        $webhookDTO = WebhookPayloadDTO::create(
            event:   $event,
            payload: $payload,
            secret:  $subscription->secret
        );

        try {
            $response = Http::timeout(config('webhook.timeout', 30))
                ->withHeaders(array_merge(
                    [
                        'Content-Type'         => 'application/json',
                        'X-Webhook-Event'      => $event,
                        'X-Webhook-ID'         => $webhookDTO->webhookId,
                        'X-Webhook-Signature'  => $webhookDTO->signature,
                        'X-Webhook-Timestamp'  => $webhookDTO->timestamp,
                    ],
                    $subscription->headers ?? []
                ))
                ->post($subscription->url, $webhookDTO->toArray());

            if ($response->successful()) {
                $subscription->update([
                    'failure_count'      => 0,
                    'last_triggered_at'  => now(),
                ]);
                Log::info('Webhook delivered', [
                    'url'   => $subscription->url,
                    'event' => $event,
                ]);
            } else {
                $this->handleWebhookFailure($subscription, $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Webhook delivery failed', [
                'url'   => $subscription->url,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            $this->handleWebhookFailure($subscription, 0);
        }
    }

    private function handleWebhookFailure(WebhookSubscription $subscription, int $statusCode): void
    {
        $failureCount = $subscription->failure_count + 1;
        $isActive     = $failureCount < 10; // Disable after 10 consecutive failures

        $subscription->update([
            'failure_count' => $failureCount,
            'is_active'     => $isActive,
        ]);

        Log::warning('Webhook delivery failed', [
            'url'          => $subscription->url,
            'status_code'  => $statusCode,
            'failure_count'=> $failureCount,
        ]);
    }
}
