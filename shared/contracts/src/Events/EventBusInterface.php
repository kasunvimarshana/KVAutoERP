<?php

declare(strict_types=1);

namespace Saas\Contracts\Events;

/**
 * In-process event bus contract for publishing and subscribing to domain events.
 *
 * This interface is intentionally decoupled from the message broker so that
 * services can use an in-memory bus for testing and swap to a broker-backed
 * implementation in production without changing subscriber code.
 */
interface EventBusInterface
{
    /**
     * Dispatches a domain event to all registered handlers.
     *
     * Implementations MUST deliver the event synchronously to all handlers
     * registered via {@see subscribe()} and {@see subscribeAll()}.  Async
     * fan-out to external brokers is a separate concern handled by outbox
     * middleware or infrastructure listeners.
     *
     * @param DomainEventInterface $event The event to dispatch.
     *
     * @throws \Throwable Implementations MAY re-throw exceptions from handlers
     *                    or wrap them in a bus-specific exception type.
     */
    public function publish(DomainEventInterface $event): void;

    /**
     * Registers a handler that will be invoked for events with the given name.
     *
     * Multiple handlers MAY be registered for the same event name; they will
     * all be invoked in registration order.
     *
     * @param string   $eventName Dot-namespaced event name, e.g. `inventory.product.stock_adjusted`.
     * @param callable $handler   Callable receiving a {@see DomainEventInterface} as its sole argument.
     */
    public function subscribe(string $eventName, callable $handler): void;

    /**
     * Registers a catch-all handler invoked for every event published on the bus.
     *
     * Useful for auditing, logging, or forwarding all events to a message broker.
     *
     * @param callable $handler Callable receiving a {@see DomainEventInterface} as its sole argument.
     */
    public function subscribeAll(callable $handler): void;
}
