<?php

namespace App\Http\Controllers;

use App\Application\Services\NotificationService;
use App\Application\Services\WebhookService;
use App\Domain\Notification\Entities\WebhookLog;
use App\Domain\Notification\Entities\WebhookRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService      $webhookService,
        private NotificationService $notificationService,
    ) {}

    /**
     * List all webhooks for the authenticated tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID');

        $webhooks = WebhookRegistration::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($webhooks);
    }

    /**
     * Show a single webhook registration.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $webhook = WebhookRegistration::where('id', $id)
            ->where('tenant_id', $request->header('X-Tenant-ID'))
            ->firstOrFail();

        return response()->json(['data' => $webhook]);
    }

    /**
     * Register a new webhook endpoint.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'url'         => ['required', 'url', 'max:500'],
            'events'      => ['required', 'array', 'min:1'],
            'events.*'    => ['required', 'string'],
            'secret'      => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $tenantId = $request->header('X-Tenant-ID');
        $result   = $this->webhookService->register(
            $tenantId,
            $request->input('url'),
            $request->input('events'),
            $request->input('secret', '')
        );

        // Persist optional description
        if ($request->filled('description')) {
            WebhookRegistration::where('id', $result['id'])->update([
                'description' => $request->input('description'),
            ]);
        }

        return response()->json(['data' => $result], 201);
    }

    /**
     * Update a webhook registration.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'url'         => ['sometimes', 'url', 'max:500'],
            'events'      => ['sometimes', 'array', 'min:1'],
            'events.*'    => ['string'],
            'is_active'   => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $webhook = WebhookRegistration::where('id', $id)
            ->where('tenant_id', $request->header('X-Tenant-ID'))
            ->firstOrFail();

        $webhook->update($request->only(['url', 'events', 'is_active', 'description']));

        return response()->json(['data' => $webhook->fresh()]);
    }

    /**
     * Delete a webhook registration.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        WebhookRegistration::where('id', $id)
            ->where('tenant_id', $request->header('X-Tenant-ID'))
            ->firstOrFail()
            ->delete();

        return response()->json(['message' => 'Webhook deleted successfully']);
    }

    /**
     * Fire a test payload at the registered endpoint.
     */
    public function test(Request $request, int $id): JsonResponse
    {
        $webhook = WebhookRegistration::where('id', $id)
            ->where('tenant_id', $request->header('X-Tenant-ID'))
            ->firstOrFail();

        try {
            $this->webhookService->dispatch(
                $webhook->tenant_id,
                'webhook.test',
                [
                    'tenant_id'  => $webhook->tenant_id,
                    'message'    => 'This is a test webhook delivery.',
                    'webhook_id' => $webhook->id,
                ]
            );

            return response()->json(['message' => 'Test webhook dispatched successfully']);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Test webhook failed', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Return delivery logs for a specific webhook.
     */
    public function logs(Request $request, int $id): JsonResponse
    {
        // Verify ownership
        WebhookRegistration::where('id', $id)
            ->where('tenant_id', $request->header('X-Tenant-ID'))
            ->firstOrFail();

        $logs = WebhookLog::where('webhook_id', $id)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($logs);
    }
}
