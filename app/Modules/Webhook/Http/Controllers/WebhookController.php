<?php

declare(strict_types=1);

namespace App\Modules\Webhook\Http\Controllers;

use App\Modules\Webhook\Application\Services\WebhookService;
use App\Modules\Webhook\Http\Requests\StoreWebhookRequest;
use App\Modules\Webhook\Http\Resources\WebhookResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * WebhookController
 */
class WebhookController
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->has('per_page') ? (int) $request->input('per_page') : null;
        $page    = max(1, (int) $request->input('page', 1));
        $webhooks = $this->webhookService->list($perPage, $page);

        return response()->json([
            'success' => true,
            'data'    => WebhookResource::collection($webhooks),
        ]);
    }

    public function store(StoreWebhookRequest $request): JsonResponse
    {
        $webhook = $this->webhookService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Webhook registered successfully.',
            'data'    => new WebhookResource($webhook),
        ], 201);
    }

    public function update(StoreWebhookRequest $request, int|string $id): JsonResponse
    {
        $webhook = $this->webhookService->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'data'    => new WebhookResource($webhook),
        ]);
    }

    public function destroy(int|string $id): JsonResponse
    {
        $this->webhookService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Webhook deleted successfully.',
        ]);
    }
}
