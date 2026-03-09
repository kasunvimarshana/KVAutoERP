<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use App\Application\Inventory\Commands\ReserveStockCommand;
use App\Application\Inventory\Commands\ReleaseStockCommand;
use App\Application\Inventory\Handlers\ReserveStockCommandHandler;
use App\Application\Inventory\Handlers\ReleaseStockCommandHandler;
use App\Shared\Contracts\MessageBrokerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Consumes inventory-related events from the message broker.
 *
 * Handles:
 *  - order.created   → reserve stock (Saga participant)
 *  - order.cancelled → release stock (compensating transaction)
 *
 * Designed to be run as a long-lived Artisan command worker process.
 */
final class InventoryEventConsumer
{
    private bool $running = true;

    public function __construct(
        private readonly MessageBrokerInterface $broker,
        private readonly ReserveStockCommandHandler $reserveHandler,
        private readonly ReleaseStockCommandHandler $releaseHandler,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Start consuming events in a loop until stopped.
     *
     * @param  string  $queue  Queue / topic name to consume from.
     */
    public function consume(string $queue = 'order.events'): void
    {
        $this->logger->info('[InventoryEventConsumer] Starting consumption', [
            'queue' => $queue,
        ]);

        $this->broker->subscribe(
            topic: $queue,
            handler: function (mixed $rawMessage, array $decoded): void {
                $this->dispatch($rawMessage, $decoded);
            },
            options: ['no_ack' => false],
        );
    }

    /**
     * Signal the consumer to stop after the current message.
     */
    public function stop(): void
    {
        $this->running = false;
        $this->logger->info('[InventoryEventConsumer] Graceful shutdown requested.');
    }

    /**
     * Dispatch a decoded message to the appropriate command handler.
     */
    private function dispatch(mixed $rawMessage, array $decoded): void
    {
        $event = $decoded['event'] ?? $decoded['type'] ?? null;

        $this->logger->debug('[InventoryEventConsumer] Received event', [
            'event'  => $event,
            'payload' => $decoded,
        ]);

        try {
            match ($event) {
                'order.created'   => $this->handleOrderCreated($decoded),
                'order.cancelled' => $this->handleOrderCancelled($decoded),
                default           => $this->logger->info(
                    '[InventoryEventConsumer] Unhandled event — skipping',
                    ['event' => $event]
                ),
            };

            $this->broker->acknowledge($rawMessage);
        } catch (Throwable $e) {
            $this->logger->error('[InventoryEventConsumer] Processing failed', [
                'event'   => $event,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // Reject without re-queue to avoid infinite poison-pill loop.
            $this->broker->reject($rawMessage, requeue: false);
        }
    }

    /**
     * Reserve stock for all line items in a newly-created order.
     */
    private function handleOrderCreated(array $payload): void
    {
        $tenantId = $payload['tenant_id'] ?? '';
        $orderId  = $payload['order_id']  ?? '';
        $items    = $payload['items']     ?? [];

        foreach ($items as $item) {
            $this->reserveHandler->handle(new ReserveStockCommand(
                productId: $item['product_id'],
                tenantId: $tenantId,
                quantity: (int) $item['quantity'],
                orderId: $orderId,
            ));
        }

        $this->logger->info('[InventoryEventConsumer] Stock reserved for order', [
            'order_id'  => $orderId,
            'item_count' => count($items),
        ]);
    }

    /**
     * Release stock for all line items of a cancelled order.
     */
    private function handleOrderCancelled(array $payload): void
    {
        $tenantId = $payload['tenant_id'] ?? '';
        $orderId  = $payload['order_id']  ?? '';
        $items    = $payload['items']     ?? [];

        foreach ($items as $item) {
            $this->releaseHandler->handle(new ReleaseStockCommand(
                productId: $item['product_id'],
                tenantId: $tenantId,
                quantity: (int) $item['quantity'],
                orderId: $orderId,
            ));
        }

        $this->logger->info('[InventoryEventConsumer] Stock released for order', [
            'order_id'   => $orderId,
            'item_count' => count($items),
        ]);
    }
}
