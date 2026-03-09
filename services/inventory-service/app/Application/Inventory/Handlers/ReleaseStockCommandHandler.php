<?php

declare(strict_types=1);

namespace App\Application\Inventory\Handlers;

use App\Application\Inventory\Commands\ReleaseStockCommand;
use App\Domain\Inventory\Events\StockReleased;
use App\Domain\Inventory\Services\StockManagementService;
use App\Shared\Contracts\MessageBrokerInterface;
use Illuminate\Support\Facades\Event;

/**
 * Handles ReleaseStockCommand.
 *
 * Acts as the compensating transaction in the Order Saga:
 * releases reserved stock when an order is cancelled.
 */
final class ReleaseStockCommandHandler
{
    public function __construct(
        private readonly StockManagementService $stockService,
        private readonly MessageBrokerInterface $messageBroker,
    ) {}

    /**
     * Execute the release command.
     */
    public function handle(ReleaseStockCommand $command): void
    {
        $this->stockService->release(
            productId: $command->productId,
            qty: $command->quantity,
            orderId: $command->orderId,
            tenantId: $command->tenantId,
        );

        $event = new StockReleased(
            productId: $command->productId,
            orderId: $command->orderId,
            quantity: $command->quantity,
            tenantId: $command->tenantId,
        );

        Event::dispatch($event);

        try {
            $this->messageBroker->publish('inventory.events', $event->toArray());
        } catch (\Throwable $e) {
            logger()->error('[ReleaseStockCommandHandler] Broker publish failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
