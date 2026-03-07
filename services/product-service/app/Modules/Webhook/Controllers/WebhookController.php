<?php

namespace App\Modules\Webhook\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Webhook\Models\WebhookSubscription;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $webhooks = WebhookSubscription::where('is_active', true)->get();
        return response()->json(['data' => $webhooks]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'url'         => ['required', 'url'],
            'events'      => ['required', 'array', 'min:1'],
            'events.*'    => ['required', 'string'],
            'secret'      => ['required', 'string', 'min:16'],
            'description' => ['sometimes', 'string'],
        ]);

        $subscription = $this->webhookService->register(
            url:         $request->input('url'),
            events:      $request->input('events'),
            secret:      $request->input('secret'),
            description: $request->input('description')
        );

        return response()->json(['data' => $subscription], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $subscription = WebhookSubscription::findOrFail($id);
        $subscription->update(['is_active' => false]);

        return response()->json(null, 204);
    }
}
