<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SuspiciousActivityDetected;
use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use App\Events\TokenRefreshed;
use App\Models\OutboxEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Ramsey\Uuid\Uuid;

/**
 * Publishes all auth domain events to the Outbox for reliable message broker delivery.
 * Implements the Outbox Pattern for at-least-once event publishing.
 */
class PublishAuthEventToOutbox implements ShouldQueue
{
    public function handleUserLoggedIn(UserLoggedIn $event): void
    {
        $this->publish('user', $event->user->id, 'auth.user_logged_in', [
            'user_id'    => $event->user->id,
            'tenant_id'  => $event->tenantId,
            'session_id' => $event->sessionId,
            'ip_address' => $event->ipAddress,
        ], $event->tenantId);
    }

    public function handleUserLoggedOut(UserLoggedOut $event): void
    {
        $this->publish('user', $event->userId, 'auth.user_logged_out', [
            'user_id'   => $event->userId,
            'tenant_id' => $event->tenantId,
            'scope'     => $event->scope,
        ], $event->tenantId);
    }

    public function handleTokenRefreshed(TokenRefreshed $event): void
    {
        $this->publish('session', $event->sessionId, 'auth.token_refreshed', [
            'user_id'    => $event->userId,
            'tenant_id'  => $event->tenantId,
            'session_id' => $event->sessionId,
        ], $event->tenantId);
    }

    public function handleSuspiciousActivity(SuspiciousActivityDetected $event): void
    {
        $this->publish('user', $event->userId, 'auth.suspicious_activity_detected', [
            'user_id'       => $event->userId,
            'tenant_id'     => $event->tenantId,
            'activity_type' => $event->activityType,
            'context'       => $event->context,
        ], $event->tenantId);
    }

    private function publish(
        string $aggregateType,
        string $aggregateId,
        string $eventType,
        array $payload,
        string $tenantId,
    ): void {
        OutboxEvent::create([
            'aggregate_type'  => $aggregateType,
            'aggregate_id'    => $aggregateId,
            'event_type'      => $eventType,
            'payload'         => $payload,
            'tenant_id'       => $tenantId,
            'idempotency_key' => Uuid::uuid4()->toString(),
            'status'          => OutboxEvent::STATUS_PENDING,
        ]);
    }
}
