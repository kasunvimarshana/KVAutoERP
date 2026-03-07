<?php

namespace App\Http\Controllers;

use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(private readonly WebhookService $webhookService) {}

    public function receiveWebhook(Request $request): JsonResponse
    {
        $signature = $request->header('X-Webhook-Signature');
        $payload   = $request->all();

        $expectedSignature = hash_hmac(
            'sha256',
            json_encode($payload['data'] ?? $payload),
            config('services.webhook_secret', '')
        );

        if (!hash_equals($expectedSignature, (string) $signature)) {
            Log::warning('Webhook signature mismatch', ['event' => $payload['event'] ?? 'unknown']);
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], 401);
        }

        $event = $payload['event'] ?? 'unknown';

        Log::info('Webhook received', [
            'event'     => $event,
            'tenant_id' => $payload['tenant_id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook received.',
            'meta'    => ['event' => $event],
        ]);
    }

    public function sendWebhook(Request $request): JsonResponse
    {
        $request->validate([
            'url'     => ['required', 'url'],
            'event'   => ['required', 'string'],
            'payload' => ['required', 'array'],
        ]);

        $success = $this->webhookService->sendWebhook(
            url:     $request->input('url'),
            event:   $request->input('event'),
            payload: $request->input('payload'),
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Webhook sent.' : 'Webhook delivery failed.',
            'meta'    => [],
        ], $success ? 200 : 500);
    }
}
