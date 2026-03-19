<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Events;

use DateTimeImmutable;
use DateTimeInterface;
use KvEnterprise\SharedKernel\Concerns\GeneratesUuid;

/**
 * Abstract base class for all domain events.
 *
 * Domain events represent significant state changes within an aggregate.
 * They are dispatched synchronously within the same service and may be
 * promoted to integration events for cross-service propagation.
 *
 * Subclasses must implement {@see getEventName()} and may override
 * {@see toArray()} to include aggregate-specific payload fields.
 */
abstract class DomainEvent
{
    use GeneratesUuid;
    /** Unique identifier for this event instance (UUID v4). */
    private readonly string $eventId;

    /** Timestamp at which this event occurred. */
    private readonly DateTimeImmutable $occurredAt;

    /**
     * @param  string  $aggregateId    The primary key of the affected aggregate.
     * @param  string  $aggregateType  Class or type name of the aggregate root.
     * @param  string  $tenantId       UUID of the owning tenant.
     */
    public function __construct(
        private readonly string $aggregateId,
        private readonly string $aggregateType,
        private readonly string $tenantId,
    ) {
        $this->eventId    = self::generateUuidV4();
        $this->occurredAt = new DateTimeImmutable();
    }

    /**
     * Return the canonical name of this event (e.g. "order.placed").
     *
     * By convention, event names are dot-separated, past-tense, and
     * scoped to their domain: "{domain}.{action_past_tense}".
     *
     * @return string
     */
    abstract public function getEventName(): string;

    /**
     * Return the unique event instance identifier.
     *
     * @return string  UUID v4 string.
     */
    public function getEventId(): string
    {
        return $this->eventId;
    }

    /**
     * Return the timestamp at which this event occurred.
     *
     * @return DateTimeImmutable
     */
    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Return the primary key of the affected aggregate.
     *
     * @return string
     */
    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * Return the type / class name of the aggregate root.
     *
     * @return string
     */
    public function getAggregateType(): string
    {
        return $this->aggregateType;
    }

    /**
     * Return the tenant identifier associated with this event.
     *
     * @return string
     */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * Serialise the event to an array suitable for persistence or transport.
     *
     * Subclasses should call parent::toArray() and merge their own payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event_id'       => $this->eventId,
            'event_name'     => $this->getEventName(),
            'aggregate_id'   => $this->aggregateId,
            'aggregate_type' => $this->aggregateType,
            'tenant_id'      => $this->tenantId,
            'occurred_at'    => $this->occurredAt->format(DateTimeInterface::ATOM),
        ];
    }
}
