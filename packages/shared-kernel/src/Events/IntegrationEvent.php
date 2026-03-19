<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Events;

/**
 * Abstract base class for integration events.
 *
 * Integration events are serialised and published to an external message
 * broker (Kafka, RabbitMQ) so that other microservices can react to
 * state changes across service boundaries.
 *
 * Each integration event carries:
 *   - All base DomainEvent fields (eventId, occurredAt, aggregateId, …).
 *   - The publishing service name (source).
 *   - A schema version for forward/backward compatibility.
 *   - A correlation ID for distributed tracing.
 *
 * Subclasses must still implement {@see DomainEvent::getEventName()}.
 */
abstract class IntegrationEvent extends DomainEvent
{
    /** Current schema version. Increment when the payload structure changes. */
    protected string $version = '1.0';

    /**
     * @param  string       $aggregateId    Primary key of the affected aggregate.
     * @param  string       $aggregateType  Class/type name of the aggregate root.
     * @param  string       $tenantId       UUID of the owning tenant.
     * @param  string       $source         Name of the publishing microservice.
     * @param  string|null  $correlationId  Distributed trace / correlation identifier.
     */
    public function __construct(
        string $aggregateId,
        string $aggregateType,
        string $tenantId,
        private readonly string $source,
        private readonly ?string $correlationId = null,
    ) {
        parent::__construct($aggregateId, $aggregateType, $tenantId);
    }

    /**
     * Return the name of the microservice that published this event.
     *
     * @return string  e.g. "order-service", "inventory-service".
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Return the schema version string for this event type.
     *
     * @return string  e.g. "1.0", "2.1".
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Return the distributed tracing correlation identifier.
     *
     * @return string|null  Null when the event was not initiated from a traced request.
     */
    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    /**
     * Return the message broker routing key for this event.
     *
     * Default pattern: "{source}.{event_name}".
     * Subclasses may override to use a custom routing key.
     *
     * @return string
     */
    public function getRoutingKey(): string
    {
        return sprintf('%s.%s', $this->source, $this->getEventName());
    }

    /**
     * {@inheritDoc}
     *
     * Merges integration-event-specific fields on top of the base payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'source'         => $this->source,
            'version'        => $this->version,
            'correlation_id' => $this->correlationId,
            'routing_key'    => $this->getRoutingKey(),
        ]);
    }
}
