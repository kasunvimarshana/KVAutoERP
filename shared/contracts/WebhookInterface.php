<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Webhook Dispatcher Contract.
 *
 * Provides a unified interface for registering, dispatching, and managing
 * outbound webhooks across the multi-tenant KV_SAAS platform.
 */
interface WebhookInterface
{
    /**
     * Dispatch a webhook event to all registered endpoints for the tenant.
     *
     * @param  string              $event     Event name (e.g. "order.created").
     * @param  array<string,mixed> $payload   Event data to send.
     * @param  string              $tenantId  Tenant whose webhooks should be triggered.
     * @return bool                           True if at least one delivery was attempted.
     */
    public function dispatch(string $event, array $payload, string $tenantId): bool;

    /**
     * Register a new webhook endpoint for the given tenant.
     *
     * @param  string        $url      HTTPS endpoint that will receive the payloads.
     * @param  array<string> $events   List of event names to subscribe to.
     * @param  string        $tenantId Owning tenant.
     * @return string                  Newly created webhook UUID.
     */
    public function register(string $url, array $events, string $tenantId): string;

    /**
     * Remove a webhook registration.
     *
     * @param  string  $webhookId  UUID of the webhook to remove.
     * @return bool                True if the webhook was found and deleted.
     */
    public function unregister(string $webhookId): bool;

    /**
     * Return all webhooks registered for a tenant.
     *
     * @param  string  $tenantId  Tenant identifier.
     * @return array<int, array{
     *     id: string,
     *     url: string,
     *     events: array<string>,
     *     is_active: bool,
     *     created_at: string
     * }>
     */
    public function getWebhooks(string $tenantId): array;

    /**
     * Re-attempt delivery of a previously failed webhook.
     *
     * @param  string  $webhookId  Delivery log UUID to retry.
     * @return bool                True if the retry was successfully dispatched.
     */
    public function retry(string $webhookId): bool;
}
