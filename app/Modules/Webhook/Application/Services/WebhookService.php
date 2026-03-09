<?php

declare(strict_types=1);

namespace App\Modules\Webhook\Application\Services;

use App\Core\Abstracts\Services\BaseService;
use App\Modules\Webhook\Domain\Models\Webhook;
use App\Modules\Webhook\Infrastructure\Repositories\WebhookRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WebhookService
 *
 * Manages webhook subscriptions and dispatches events to subscribers.
 * Payloads are signed with HMAC-SHA256 for verification by consumers.
 */
class WebhookService extends BaseService
{
    public function __construct(
        private readonly WebhookRepository $webhookRepository
    ) {}

    // -------------------------------------------------------------------------
    //  CRUD
    // -------------------------------------------------------------------------

    public function list(?int $perPage = null, int $page = 1): mixed
    {
        return $this->webhookRepository->all(perPage: $perPage, page: $page);
    }

    public function create(array $data): Webhook
    {
        return $this->webhookRepository->create($data);
    }

    public function update(int|string $id, array $data): Webhook
    {
        return $this->webhookRepository->update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->webhookRepository->delete($id);
    }

    // -------------------------------------------------------------------------
    //  Dispatch
    // -------------------------------------------------------------------------

    /**
     * Dispatch a named event to all active subscribed webhooks.
     *
     * @param  string              $event    e.g. "order.created"
     * @param  array<string,mixed> $payload
     */
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = $this->webhookRepository->findByEvent($event);

        foreach ($webhooks as $webhook) {
            $this->send($webhook, $event, $payload);
        }
    }

    /**
     * Send the webhook payload to a single endpoint, signed with HMAC-SHA256.
     */
    private function send(Webhook $webhook, string $event, array $payload): void
    {
        $body      = json_encode($payload);
        $signature = hash_hmac('sha256', $body, $webhook->secret ?? '');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type'        => 'application/json',
                    'X-Webhook-Event'     => $event,
                    'X-Webhook-Signature' => "sha256={$signature}",
                    'X-Tenant-ID'         => (string) $webhook->tenant_id,
                ])
                ->post($webhook->url, $payload);

            if (! $response->successful()) {
                Log::warning("[Webhook] Delivery to [{$webhook->url}] failed. Status: {$response->status()}");
            } else {
                Log::debug("[Webhook] Event [{$event}] delivered to [{$webhook->url}].");
            }
        } catch (\Throwable $e) {
            Log::error("[Webhook] Delivery exception for [{$webhook->url}]: {$e->getMessage()}");
        }
    }
}
