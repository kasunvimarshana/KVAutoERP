<?php

declare(strict_types=1);

namespace Shared\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Abstract Domain Event
 * 
 * Base class for all domain events across microservices.
 * Events are the primary mechanism for inter-service communication.
 */
abstract class DomainEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Unique event ID for idempotency.
     */
    public readonly string $eventId;

    /**
     * Timestamp when event occurred.
     */
    public readonly string $occurredAt;

    /**
     * Tenant ID for multi-tenant isolation.
     */
    public readonly string|int|null $tenantId;

    /**
     * Event version for schema evolution.
     */
    public readonly string $version;

    /**
     * Correlation ID for distributed tracing.
     */
    public readonly ?string $correlationId;

    public function __construct(?string $correlationId = null)
    {
        $this->eventId = (string) \Illuminate\Support\Str::uuid();
        $this->occurredAt = now()->toISOString();
        $this->tenantId = config('tenant.id');
        $this->version = $this->getVersion();
        $this->correlationId = $correlationId;
    }

    /**
     * Get the event name for message broker routing.
     * 
     * @return string
     */
    abstract public function getEventName(): string;

    /**
     * Get the event payload for serialization.
     * 
     * @return array
     */
    abstract public function getPayload(): array;

    /**
     * Get the event version for schema evolution.
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return '1.0';
    }

    /**
     * Serialize the event for message broker transmission.
     * 
     * @return array
     */
    public function toMessage(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_name' => $this->getEventName(),
            'version' => $this->version,
            'occurred_at' => $this->occurredAt,
            'tenant_id' => $this->tenantId,
            'correlation_id' => $this->correlationId,
            'payload' => $this->getPayload(),
        ];
    }
}
