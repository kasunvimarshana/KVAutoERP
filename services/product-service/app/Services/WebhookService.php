<?php

namespace App\Services;

use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_MS = 500;

    public function sendWebhook(string $url, string $event, array $payload): bool
    {
        $tenantId  = app('tenant.id') ?? null;
        $signature = $this->generateSignature($payload);

        $structuredPayload = [
            'event'     => $event,
            'tenant_id' => $tenantId,
            'timestamp' => now()->toIso8601String(),
            'data'      => $payload,
        ];

        $log = WebhookLog::create([
            'tenant_id' => $tenantId,
            'event'     => $event,
            'url'       => $url,
            'payload'   => $structuredPayload,
            'status'    => 'pending',
            'attempts'  => 0,
        ]);

        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            $log->increment('attempts');

            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'X-Webhook-Event'     => $event,
                        'X-Webhook-Signature' => $signature,
                        'X-Service-Token'     => config('services.service_token'),
                        'Content-Type'        => 'application/json',
                    ])
                    ->post($url, $structuredPayload);

                $responseData = [
                    'status_code' => $response->status(),
                    'body'        => $response->json() ?? $response->body(),
                ];

                if ($response->successful()) {
                    $log->update(['status' => 'delivered', 'response' => $responseData]);
                    return true;
                }

                $log->update(['status' => 'failed', 'response' => $responseData]);

                Log::warning('Webhook delivery failed', [
                    'url'      => $url,
                    'event'    => $event,
                    'attempt'  => $attempt,
                    'status'   => $response->status(),
                ]);

            } catch (\Throwable $e) {
                $lastException = $e;
                $log->update([
                    'status'   => 'failed',
                    'response' => ['error' => $e->getMessage()],
                ]);

                Log::warning('Webhook delivery exception', [
                    'url'     => $url,
                    'event'   => $event,
                    'attempt' => $attempt,
                    'error'   => $e->getMessage(),
                ]);
            }

            if ($attempt < self::MAX_RETRIES) {
                usleep(self::RETRY_DELAY_MS * 1000 * $attempt);
            }
        }

        Log::error('Webhook delivery exhausted retries', [
            'url'   => $url,
            'event' => $event,
            'error' => $lastException?->getMessage(),
        ]);

        return false;
    }

    private function generateSignature(array $payload): string
    {
        $secret = config('services.webhook_secret', '');
        return hash_hmac('sha256', json_encode($payload), $secret);
    }
}
