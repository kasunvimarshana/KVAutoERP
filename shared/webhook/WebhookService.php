<?php

declare(strict_types=1);

namespace App\Shared\Webhook;

use App\Shared\Contracts\WebhookInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Webhook Service.
 *
 * Complete implementation of {@see WebhookInterface} that:
 *  - Stores webhook registrations in the `webhooks` DB table.
 *  - Stores delivery attempts in the `webhook_deliveries` table.
 *  - Signs payloads with HMAC-SHA256 (X-Webhook-Signature header).
 *  - Retries failed deliveries with exponential back-off.
 *  - Uses Guzzle for HTTP delivery.
 *
 * Database schema expected:
 *
 *   webhooks (id, tenant_id, url, events JSON, secret, is_active, created_at, updated_at)
 *   webhook_deliveries (id, webhook_id, event, payload JSON, response_code,
 *                       response_body, attempts, next_retry_at, delivered_at,
 *                       created_at, updated_at)
 */
final class WebhookService implements WebhookInterface
{
    private const string WEBHOOKS_TABLE   = 'webhooks';
    private const string DELIVERIES_TABLE = 'webhook_deliveries';

    private const int MAX_RETRIES = 3;
    private const int BASE_BACKOFF_SECONDS = 60;
    private const int CONNECT_TIMEOUT = 5;
    private const int REQUEST_TIMEOUT = 30;

    public function __construct(
        private readonly GuzzleClient $http,
        private readonly string $signingSecret,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // WebhookInterface
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * Finds all active webhooks for the tenant that are subscribed to $event,
     * then attempts HTTP delivery for each one.
     */
    public function dispatch(string $event, array $payload, string $tenantId): bool
    {
        $hooks = DB::table(self::WEBHOOKS_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $dispatched = false;

        foreach ($hooks as $hook) {
            $events = is_array($hook->events)
                ? $hook->events
                : json_decode($hook->events ?? '[]', true);

            if (!in_array($event, $events, strict: true) && !in_array('*', $events, strict: true)) {
                continue;
            }

            $this->deliverWebhook(
                webhookId: $hook->id,
                url: $hook->url,
                secret: $hook->secret ?? $this->signingSecret,
                event: $event,
                payload: $payload,
            );

            $dispatched = true;
        }

        return $dispatched;
    }

    /**
     * {@inheritDoc}
     */
    public function register(string $url, array $events, string $tenantId): string
    {
        $id     = (string) Str::uuid();
        $secret = Str::random(64);

        DB::table(self::WEBHOOKS_TABLE)->insert([
            'id'         => $id,
            'tenant_id'  => $tenantId,
            'url'        => $url,
            'events'     => json_encode($events, JSON_THROW_ON_ERROR),
            'secret'     => $secret,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('[Webhook] Registered', [
            'id'        => $id,
            'tenant_id' => $tenantId,
            'url'       => $url,
            'events'    => $events,
        ]);

        return $id;
    }

    /**
     * {@inheritDoc}
     */
    public function unregister(string $webhookId): bool
    {
        $deleted = DB::table(self::WEBHOOKS_TABLE)
            ->where('id', $webhookId)
            ->delete();

        return $deleted > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getWebhooks(string $tenantId): array
    {
        return DB::table(self::WEBHOOKS_TABLE)
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($hook): array {
                return [
                    'id'         => $hook->id,
                    'url'        => $hook->url,
                    'events'     => json_decode($hook->events ?? '[]', true),
                    'is_active'  => (bool) $hook->is_active,
                    'created_at' => $hook->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * {@inheritDoc}
     *
     * Re-dispatches the delivery identified by $webhookId, only if it
     * has not already been successfully delivered.
     */
    public function retry(string $webhookId): bool
    {
        $delivery = DB::table(self::DELIVERIES_TABLE)
            ->where('id', $webhookId)
            ->whereNull('delivered_at')
            ->first();

        if ($delivery === null) {
            return false;
        }

        $hook = DB::table(self::WEBHOOKS_TABLE)
            ->where('id', $delivery->webhook_id)
            ->first();

        if ($hook === null) {
            return false;
        }

        $payload = json_decode($delivery->payload ?? '{}', associative: true);

        $this->deliverWebhook(
            webhookId: $hook->id,
            url: $hook->url,
            secret: $hook->secret ?? $this->signingSecret,
            event: $delivery->event,
            payload: $payload,
            deliveryId: $delivery->id,
        );

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private delivery logic
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Attempt HTTP delivery with exponential back-off retry on failure.
     *
     * @param  string       $webhookId   Webhook registration UUID.
     * @param  string       $url         Target endpoint.
     * @param  string       $secret      Per-webhook HMAC secret.
     * @param  string       $event       Event name.
     * @param  array        $payload     Event data.
     * @param  string|null  $deliveryId  Existing delivery row ID (for retries).
     */
    private function deliverWebhook(
        string $webhookId,
        string $url,
        string $secret,
        string $event,
        array $payload,
        ?string $deliveryId = null,
    ): void {
        $deliveryId ??= (string) Str::uuid();
        $encodedPayload = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $signature      = $this->sign($encodedPayload, $secret);

        if (!DB::table(self::DELIVERIES_TABLE)->where('id', $deliveryId)->exists()) {
            DB::table(self::DELIVERIES_TABLE)->insert([
                'id'         => $deliveryId,
                'webhook_id' => $webhookId,
                'event'      => $event,
                'payload'    => $encodedPayload,
                'attempts'   => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $attempt = (int) DB::table(self::DELIVERIES_TABLE)
            ->where('id', $deliveryId)
            ->value('attempts');

        if ($attempt >= self::MAX_RETRIES) {
            Log::warning('[Webhook] Max retries exceeded', [
                'delivery_id' => $deliveryId,
                'url'         => $url,
            ]);
            return;
        }

        try {
            $response = $this->http->post($url, [
                'headers' => [
                    'Content-Type'         => 'application/json',
                    'X-Webhook-Event'      => $event,
                    'X-Webhook-Signature'  => "sha256={$signature}",
                    'X-Webhook-Delivery'   => $deliveryId,
                    'X-Webhook-Timestamp'  => (string) time(),
                    'User-Agent'           => 'KV-SAAS-Webhook/1.0',
                ],
                'body'            => $encodedPayload,
                'connect_timeout' => self::CONNECT_TIMEOUT,
                'timeout'         => self::REQUEST_TIMEOUT,
                'http_errors'     => false,
            ]);

            $statusCode   = $response->getStatusCode();
            $responseBody = substr((string) $response->getBody(), 0, 1000);
            $succeeded    = $statusCode >= 200 && $statusCode < 300;

            DB::table(self::DELIVERIES_TABLE)->where('id', $deliveryId)->update([
                'response_code' => $statusCode,
                'response_body' => $responseBody,
                'attempts'      => $attempt + 1,
                'delivered_at'  => $succeeded ? now() : null,
                'next_retry_at' => $succeeded ? null : now()->addSeconds($this->backoffSeconds($attempt + 1)),
                'updated_at'    => now(),
            ]);

            Log::info('[Webhook] Delivered', [
                'delivery_id' => $deliveryId,
                'url'         => $url,
                'event'       => $event,
                'status'      => $statusCode,
            ]);
        } catch (RequestException|\Throwable $e) {
            DB::table(self::DELIVERIES_TABLE)->where('id', $deliveryId)->update([
                'attempts'      => $attempt + 1,
                'response_body' => $e->getMessage(),
                'next_retry_at' => now()->addSeconds($this->backoffSeconds($attempt + 1)),
                'updated_at'    => now(),
            ]);

            Log::error('[Webhook] Delivery failed', [
                'delivery_id' => $deliveryId,
                'url'         => $url,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Compute the HMAC-SHA256 signature for a payload.
     *
     * @param  string  $payload
     * @param  string  $secret
     * @return string  Hex digest.
     */
    private function sign(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Calculate exponential back-off delay in seconds.
     *
     * Attempt 1 → 60 s, Attempt 2 → 120 s, Attempt 3 → 240 s.
     *
     * @param  int  $attempt  1-indexed attempt number.
     * @return int            Delay in seconds.
     */
    private function backoffSeconds(int $attempt): int
    {
        return self::BASE_BACKOFF_SECONDS * (2 ** ($attempt - 1));
    }
}
