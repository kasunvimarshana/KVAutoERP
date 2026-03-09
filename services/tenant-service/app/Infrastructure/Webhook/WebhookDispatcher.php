<?php

declare(strict_types=1);

namespace App\Infrastructure\Webhook;

use App\Domain\Webhook\Entities\WebhookSubscription;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Dispatches HTTP webhook callbacks with HMAC-SHA256 signing and
 * exponential-backoff retry logic. All delivery attempts are logged
 * to the webhook_deliveries table.
 */
final class WebhookDispatcher
{
    private readonly Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'timeout'         => 10,
            'connect_timeout' => 5,
            'verify'          => true,
            'headers'         => [
                'Content-Type' => 'application/json',
                'User-Agent'   => 'TenantService-Webhook/1.0',
            ],
        ]);
    }

    /**
     * Send a webhook for the given event. Retries up to $subscription->retry_count
     * times using exponential back-off.
     */
    public function dispatch(WebhookSubscription $subscription, string $event, array $payload): void
    {
        $body = json_encode([
            'event'     => $event,
            'tenant_id' => $subscription->tenant_id,
            'timestamp' => now()->toIso8601String(),
            'payload'   => $payload,
        ], JSON_THROW_ON_ERROR);

        $signature = $subscription->getSignatureHeader($body);

        $maxAttempts = max(1, $subscription->retry_count);
        $lastError   = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = $this->http->post($subscription->url, [
                    'body'    => $body,
                    'headers' => [
                        'X-Webhook-Signature' => $signature,
                        'X-Webhook-Event'     => $event,
                        'X-Webhook-ID'        => $subscription->id,
                        'X-Delivery-Attempt'  => (string) $attempt,
                    ],
                ]);

                $statusCode = $response->getStatusCode();

                $this->logDelivery($subscription, $event, $body, $attempt, $statusCode, null);

                Log::info('Webhook delivered', [
                    'subscription_id' => $subscription->id,
                    'event'           => $event,
                    'status'          => $statusCode,
                    'attempt'         => $attempt,
                ]);

                return; // Success
            } catch (RequestException $e) {
                $statusCode = $e->getResponse()?->getStatusCode() ?? 0;
                $lastError  = $e->getMessage();

                $this->logDelivery($subscription, $event, $body, $attempt, $statusCode, $lastError);

                Log::warning('Webhook delivery failed', [
                    'subscription_id' => $subscription->id,
                    'event'           => $event,
                    'attempt'         => $attempt,
                    'max_attempts'    => $maxAttempts,
                    'error'           => $lastError,
                ]);

                if ($attempt < $maxAttempts) {
                    // Exponential back-off: 1s, 2s, 4s, 8s …
                    sleep((int) (2 ** ($attempt - 1)));
                }
            } catch (Throwable $e) {
                $lastError = $e->getMessage();

                $this->logDelivery($subscription, $event, $body, $attempt, 0, $lastError);

                Log::error('Webhook unexpected error', [
                    'subscription_id' => $subscription->id,
                    'error'           => $lastError,
                ]);

                break; // Non-recoverable
            }
        }

        Log::error('Webhook delivery exhausted all retries', [
            'subscription_id' => $subscription->id,
            'event'           => $event,
            'last_error'      => $lastError,
        ]);
    }

    /**
     * Retry the most recent failed delivery for a subscription.
     */
    public function dispatchRetry(WebhookSubscription $subscription): void
    {
        $delivery = DB::table('webhook_deliveries')
            ->where('subscription_id', $subscription->id)
            ->whereNull('response_status')
            ->orWhere('response_status', '>=', 400)
            ->orderByDesc('created_at')
            ->first();

        if ($delivery === null) {
            Log::info('No failed delivery to retry', ['subscription_id' => $subscription->id]);

            return;
        }

        $payload = json_decode($delivery->payload ?? '{}', true);

        $this->dispatch($subscription, $delivery->event, $payload['payload'] ?? []);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function logDelivery(
        WebhookSubscription $subscription,
        string $event,
        string $payload,
        int $attempt,
        int $statusCode,
        ?string $error,
    ): void {
        try {
            DB::table('webhook_deliveries')->insert([
                'id'              => \Illuminate\Support\Str::uuid()->toString(),
                'subscription_id' => $subscription->id,
                'tenant_id'       => $subscription->tenant_id,
                'event'           => $event,
                'payload'         => $payload,
                'attempt_number'  => $attempt,
                'response_status' => $statusCode ?: null,
                'error_message'   => $error,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to log webhook delivery', ['error' => $e->getMessage()]);
        }
    }
}
