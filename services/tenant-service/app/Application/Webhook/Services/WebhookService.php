<?php

declare(strict_types=1);

namespace App\Application\Webhook\Services;

use App\Application\Webhook\DTOs\WebhookSubscriptionDTO;
use App\Domain\Webhook\Entities\WebhookSubscription;
use App\Infrastructure\Webhook\WebhookDispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class WebhookService
{
    public function __construct(
        private readonly WebhookDispatcher $dispatcher,
    ) {}

    public function createSubscription(string $tenantId, array $data): WebhookSubscriptionDTO
    {
        $subscription = WebhookSubscription::create([
            'tenant_id'   => $tenantId,
            'url'         => $data['url'],
            'events'      => $data['events'],
            'secret'      => $data['secret'] ?? Str::random(40),
            'is_active'   => $data['is_active'] ?? true,
            'retry_count' => $data['retry_count'] ?? 3,
        ]);

        Log::info('Webhook subscription created', [
            'id'        => $subscription->id,
            'tenant_id' => $tenantId,
            'url'       => $subscription->url,
        ]);

        return WebhookSubscriptionDTO::fromEntity($subscription);
    }

    public function updateSubscription(string $id, string $tenantId, array $data): WebhookSubscriptionDTO
    {
        $subscription = WebhookSubscription::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($subscription === null) {
            throw new RuntimeException("Webhook subscription '{$id}' not found.", 404);
        }

        $allowed = ['url', 'events', 'secret', 'is_active', 'retry_count'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $subscription->$field = $data[$field];
            }
        }

        $subscription->save();

        return WebhookSubscriptionDTO::fromEntity($subscription);
    }

    public function deleteSubscription(string $id, string $tenantId): void
    {
        $subscription = WebhookSubscription::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($subscription === null) {
            throw new RuntimeException("Webhook subscription '{$id}' not found.", 404);
        }

        $subscription->delete();

        Log::info('Webhook subscription deleted', ['id' => $id]);
    }

    public function getSubscription(string $id, string $tenantId): WebhookSubscriptionDTO
    {
        $subscription = WebhookSubscription::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($subscription === null) {
            throw new RuntimeException("Webhook subscription '{$id}' not found.", 404);
        }

        return WebhookSubscriptionDTO::fromEntity($subscription);
    }

    /**
     * @return Collection<int, WebhookSubscriptionDTO>
     */
    public function getSubscriptions(string $tenantId): Collection
    {
        return WebhookSubscription::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (WebhookSubscription $s) => WebhookSubscriptionDTO::fromEntity($s));
    }

    /**
     * Trigger all active subscriptions for a given event.
     */
    public function triggerWebhook(string $tenantId, string $event, array $payload): void
    {
        $subscriptions = WebhookSubscription::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->shouldReceive($event)) {
                $this->dispatcher->dispatch($subscription, $event, $payload);

                $subscription->last_triggered_at = now();
                $subscription->save();
            }
        }
    }

    /**
     * Retry the last failed delivery for a subscription.
     */
    public function retryWebhook(string $webhookId): void
    {
        $subscription = WebhookSubscription::find($webhookId);

        if ($subscription === null) {
            throw new RuntimeException("Webhook subscription '{$webhookId}' not found.", 404);
        }

        $this->dispatcher->dispatchRetry($subscription);
    }

    /**
     * Verify an incoming HMAC-SHA256 webhook signature.
     */
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
