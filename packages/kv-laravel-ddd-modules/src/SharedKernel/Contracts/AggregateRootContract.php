<?php

declare(strict_types=1);

namespace LaravelDDD\SharedKernel\Contracts;

/**
 * Contract for aggregate roots.
 *
 * Aggregate roots are the entry point for a cluster of domain objects.
 * They record domain events that occurred during state transitions.
 */
interface AggregateRootContract extends EntityContract
{
    /**
     * Record a domain event that occurred within this aggregate.
     *
     * @param  object  $event  Any domain event object.
     * @return void
     */
    public function recordEvent(object $event): void;

    /**
     * Return and clear all recorded domain events.
     *
     * @return list<object>
     */
    public function releaseEvents(): array;
}
