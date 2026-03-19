<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Contracts\Messaging;

use KvEnterprise\SharedKernel\Events\DomainEvent;
use KvEnterprise\SharedKernel\Events\IntegrationEvent;

/**
 * Contract for publishing domain and integration events to the message bus.
 *
 * Wraps the raw MessageBusInterface with event-aware serialisation and
 * routing logic. Implementations should use the Outbox Pattern to ensure
 * events are not lost when the database transaction rolls back.
 */
interface EventPublisherInterface
{
    /**
     * Publish a single integration event to the message bus.
     *
     * The event is serialised via its {@see IntegrationEvent::toArray()} method.
     * Routing to the appropriate topic is determined by the event name.
     *
     * @param  IntegrationEvent  $event  The event to publish.
     * @return bool                       True when the broker accepted the message.
     */
    public function publish(IntegrationEvent $event): bool;

    /**
     * Publish multiple integration events atomically (transactional outbox).
     *
     * All events must be accepted by the broker or none are considered
     * delivered. Implementations should leverage the Outbox Pattern to
     * guarantee delivery even during partial failures.
     *
     * @param  array<int, IntegrationEvent>  $events  Events to publish in batch.
     * @return bool                                     True when all events were accepted.
     */
    public function publishBatch(array $events): bool;

    /**
     * Schedule an integration event for future delivery.
     *
     * @param  IntegrationEvent  $event    The event to publish.
     * @param  int               $delay    Delay in seconds before delivery.
     * @return bool                         True when the broker accepted the scheduled message.
     */
    public function publishScheduled(IntegrationEvent $event, int $delay): bool;

    /**
     * Publish a domain event for local in-process subscribers.
     *
     * Domain events are dispatched synchronously within the same service
     * and are not forwarded to external brokers.
     *
     * @param  DomainEvent  $event  The domain event to dispatch.
     * @return void
     */
    public function publishDomainEvent(DomainEvent $event): void;
}
