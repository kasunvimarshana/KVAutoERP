<?php

declare(strict_types=1);

namespace App\Application\Inventory\Handlers;

use App\Application\Inventory\Commands\ReserveStockCommand;
use App\Domain\Inventory\Events\StockReserved;
use App\Domain\Inventory\Services\StockManagementService;
use App\Shared\Contracts\MessageBrokerInterface;
use Illuminate\Support\Facades\Event;
use RuntimeException;

/**
 * Handles ReserveStockCommand.
 *
 * Acts as a Saga participant: reserves stock for an order and emits a
 * StockReserved event confirming the reservation.
 */
final class ReserveStockCommandHandler
{
    public function __construct(
        private readonly StockManagementService $stockService,
        private readonly MessageBrokerInterface $messageBroker,
    ) {}

    /**
     * Execute the reserve command.
     *
     * @throws RuntimeException When stock is insufficient.
     */
    public function handle(ReserveStockCommand $command): bool
    {
        $reserved = $this->stockService->reserve(
            productId: $command->productId,
            qty: $command->quantity,
            orderId: $command->orderId,
            tenantId: $command->tenantId,
        );

        if ($reserved) {
            $event = new StockReserved(
                productId: $command->productId,
                orderId: $command->orderId,
                quantity: $command->quantity,
                tenantId: $command->tenantId,
            );

            Event::dispatch($event);

            try {
                $this->messageBroker->publish('inventory.events', $event->toArray());
            } catch (\Throwable $e) {
                logger()->error('[ReserveStockCommandHandler] Broker publish failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $reserved;
    }
}
