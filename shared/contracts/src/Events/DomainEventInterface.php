<?php

declare(strict_types=1);

namespace Saas\Contracts\Events;

/**
 * Contract for a domain event emitted by an aggregate root.
 *
 * Domain events are immutable records of something that happened within the
 * domain.  They carry enough information to rebuild projections, trigger
 * downstream side-effects, and support event sourcing.
 */
interface DomainEventInterface
{
    /**
     * Returns the globally unique identifier for this specific event occurrence.
     *
     * Should be a UUID v4 or ULID generated at event creation time.
     */
    public function getEventId(): string;

    /**
     * Returns a dot-namespaced name that uniquely identifies the event type.
     *
     * Convention: `{bounded-context}.{aggregate}.{past-tense-verb}`
     * Example: `inventory.product.stock_adjusted`
     */
    public function getEventName(): string;

    /**
     * Returns the identifier of the aggregate that raised this event.
     */
    public function getAggregateId(): string;

    /**
     * Returns the class or logical type name of the aggregate root.
     *
     * Example: `Product`, `Order`, `Tenant`
     */
    public function getAggregateType(): string;

    /**
     * Returns the identifier of the tenant within whose context the event occurred.
     */
    public function getTenantId(): string;

    /**
     * Returns the event payload — the data that describes what changed.
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array;

    /**
     * Returns the instant at which the domain event was raised.
     */
    public function getOccurredAt(): \DateTimeImmutable;

    /**
     * Returns the sequential version (stream position) of the event within the
     * aggregate's event stream, enabling optimistic concurrency control.
     *
     * Starts at `1` for the first event of a new aggregate.
     */
    public function getVersion(): int;
}
