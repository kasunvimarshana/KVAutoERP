<?php

namespace App\Application\Services;

use App\Domain\Notification\Entities\WebhookLog;
use App\Domain\Notification\Entities\WebhookRegistration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookService
{
    private const MAX_RETRIES    = 3;
    private const BACKOFF_BASE   = 60; // seconds

    // -------------------------------------------------------------------------
    // Registration management
    // -------------------------------------------------------------------------

    /**
     * Register a new webhook endpoint for a tenant.
     */
    public function register(string $tenantId, string $url, array $events, string $secret = ''): array
    {
        if ($secret === '') {
            $secret = Str::random(32);
        }

        $webhook = WebhookRegistration::create([
            'tenant_id' => $tenantId,
            'url'       => $url,
            'events'    => $events,
            'secret'    => $secret,
            'is_active' => true,
        ]);

        Log::info('Webhook registered', [
            'tenant_id'  => $tenantId,
            'webhook_id' => $webhook->id,
            'url'        => $url,
            'events'     => $events,
        ]);

        return [
            'id'        => $webhook->id,
            'url'       => $webhook->url,
            'events'    => $webhook->events,
            'secret'    => $secret, // Return only on creation
            'is_active' => $webhook->is_active,
        ];
    }

    /**
     * Dispatch an event to all matching active webhooks for a tenant,
     * with exponential-backoff retry logic.
     */
    public function dispatch(string $tenantId, string $event, array $payload): void
    {
        $webhooks = WebhookRegistration::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->filter(fn ($w) => in_array($event, $w->events, true) || in_array('*', $w->events, true));

        foreach ($webhooks as $webhook) {
            $this->deliverWithRetry($webhook, $event, $payload);
        }
    }

    /**
     * Verify that the incoming signature matches the expected HMAC-SHA256 value.
     */
    public function verify(string $payload, string $signature, string $secret): bool
    {
        // Accept both raw hex and "sha256=<hex>" format
        $incoming = str_starts_with($signature, 'sha256=')
            ? substr($signature, 7)
            : $signature;

        $expected = $this->generateSignature($payload, $secret);

        return hash_equals($expected, $incoming);
    }

    /**
     * Generate an HMAC-SHA256 signature for the given payload.
     */
    public function generateSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    // -------------------------------------------------------------------------
    // Delivery with retry
    // -------------------------------------------------------------------------

    private function deliverWithRetry(WebhookRegistration $webhook, string $event, array $payload): void
    {
        $jsonPayload = json_encode([
            'event'     => $event,
            'timestamp' => now()->toIso8601String(),
            'data'      => $payload,
        ]);

        $signature = $this->generateSignature($jsonPayload, $webhook->secret ?? '');

        $log = WebhookLog::create([
            'webhook_id' => $webhook->id,
            'tenant_id'  => $webhook->tenant_id,
            'event'      => $event,
            'payload'    => $payload,
            'attempts'   => 0,
            'delivered'  => false,
        ]);

        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            $attempt++;
            $log->increment('attempts');

            try {
                $response = Http::withHeaders([
                    'Content-Type'        => 'application/json',
                    'X-Webhook-Event'     => $event,
                    'X-Webhook-Signature' => 'sha256=' . $signature,
                    'X-Webhook-ID'        => (string) $webhook->id,
                ])->timeout(10)->post($webhook->url, json_decode($jsonPayload, true));

                if ($response->successful()) {
                    $log->update([
                        'delivered'       => true,
                        'delivered_at'    => now(),
                        'response_status' => $response->status(),
                        'response_body'   => substr($response->body(), 0, 1000),
                        'error_message'   => null,
                    ]);

                    Log::info('Webhook delivered', [
                        'webhook_id' => $webhook->id,
                        'event'      => $event,
                        'attempt'    => $attempt,
                    ]);

                    return;
                }

                $error = 'HTTP ' . $response->status();
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }

            Log::warning('Webhook delivery attempt failed', [
                'webhook_id' => $webhook->id,
                'event'      => $event,
                'attempt'    => $attempt,
                'error'      => $error,
            ]);

            // Exponential backoff between retries (do not sleep on last attempt)
            if ($attempt < self::MAX_RETRIES) {
                $backoff = self::BACKOFF_BASE * (2 ** ($attempt - 1)); // 60, 120, 240 …
                sleep($backoff);
            }
        }

        // All retries exhausted – move to dead-letter state
        $log->update([
            'delivered'       => false,
            'response_status' => $log->response_status,
            'error_message'   => $error ?? 'Max retries exceeded',
        ]);

        Log::error('Webhook delivery failed after all retries – dead-lettered', [
            'webhook_id' => $webhook->id,
            'event'      => $event,
        ]);
    }
}
