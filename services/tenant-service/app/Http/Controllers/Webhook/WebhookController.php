<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Application\Webhook\Services\WebhookService;
use App\Http\Requests\Webhook\CreateWebhookRequest;
use App\Http\Requests\Webhook\UpdateWebhookRequest;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WebhookController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly WebhookService $webhookService,
    ) {}

    /**
     * GET /api/webhooks
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tenantId     = (string) ($request->header('X-Tenant-ID') ?? $request->query('tenant_id', ''));
            $subscriptions = $this->webhookService->getSubscriptions($tenantId);

            return $this->success($subscriptions->toArray());
        } catch (Throwable $e) {
            Log::error('Failed to list webhooks', ['error' => $e->getMessage()]);

            return $this->serverError('Failed to retrieve webhook subscriptions.');
        }
    }

    /**
     * POST /api/webhooks
     */
    public function store(CreateWebhookRequest $request): JsonResponse
    {
        try {
            $tenantId = (string) ($request->header('X-Tenant-ID') ?? $request->input('tenant_id', ''));
            $dto      = $this->webhookService->createSubscription($tenantId, $request->validated());

            return $this->created($dto, 'Webhook subscription created.');
        } catch (Throwable $e) {
            Log::error('Failed to create webhook', ['error' => $e->getMessage()]);

            return $this->serverError('Failed to create webhook subscription.');
        }
    }

    /**
     * GET /api/webhooks/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $tenantId = (string) ($request->header('X-Tenant-ID') ?? $request->query('tenant_id', ''));
            $dto      = $this->webhookService->getSubscription($id, $tenantId);

            return $this->success($dto);
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            return $this->serverError('Failed to retrieve webhook subscription.');
        }
    }

    /**
     * PUT /api/webhooks/{id}
     */
    public function update(UpdateWebhookRequest $request, string $id): JsonResponse
    {
        try {
            $tenantId = (string) ($request->header('X-Tenant-ID') ?? $request->input('tenant_id', ''));
            $dto      = $this->webhookService->updateSubscription($id, $tenantId, $request->validated());

            return $this->success($dto, 'Webhook subscription updated.');
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to update webhook', ['id' => $id, 'error' => $e->getMessage()]);

            return $this->serverError('Failed to update webhook subscription.');
        }
    }

    /**
     * DELETE /api/webhooks/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $tenantId = (string) ($request->header('X-Tenant-ID') ?? $request->query('tenant_id', ''));
            $this->webhookService->deleteSubscription($id, $tenantId);

            return $this->noContent();
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to delete webhook', ['id' => $id, 'error' => $e->getMessage()]);

            return $this->serverError('Failed to delete webhook subscription.');
        }
    }

    /**
     * POST /api/webhooks/{id}/test
     */
    public function test(Request $request, string $id): JsonResponse
    {
        try {
            $tenantId = (string) ($request->header('X-Tenant-ID') ?? $request->query('tenant_id', ''));
            $dto      = $this->webhookService->getSubscription($id, $tenantId);

            $this->webhookService->triggerWebhook($tenantId, 'webhook.test', [
                'message'        => 'This is a test webhook delivery.',
                'subscription_id' => $dto->id,
            ]);

            return $this->success(null, 'Test webhook dispatched.');
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to send test webhook', ['id' => $id, 'error' => $e->getMessage()]);

            return $this->serverError('Failed to dispatch test webhook.');
        }
    }
}
