<?php

declare(strict_types=1);

namespace App\Application\Inventory\Handlers;

use App\Application\Inventory\Commands\AdjustStockCommand;
use App\Domain\Inventory\Events\LowStockAlert;
use App\Domain\Inventory\Events\StockAdjusted;
use App\Domain\Inventory\Services\StockManagementService;
use App\Shared\Contracts\MessageBrokerInterface;
use Illuminate\Support\Facades\Event;

/**
 * Handles AdjustStockCommand.
 *
 * Delegates to StockManagementService, fires StockAdjusted event,
 * and optionally fires LowStockAlert if the new quantity is low.
 */
final class AdjustStockCommandHandler
{
    public function __construct(
        private readonly StockManagementService $stockService,
        private readonly MessageBrokerInterface $messageBroker,
    ) {}

    /**
     * Execute the adjustment command.
     *
     * @return array<string, mixed>  The resulting StockMovement.
     */
    public function handle(AdjustStockCommand $command): array
    {
        $movement = $this->stockService->adjust(
            productId: $command->productId,
            newQty: $command->newQuantity,
            reason: $command->reason,
            performedBy: $command->performedBy,
            tenantId: $command->tenantId,
        );

        // Fire domain event.
        $adjustedEvent = new StockAdjusted(
            productId: $command->productId,
            previousQty: $movement->previousQuantity,
            newQty: $movement->newQuantity,
            reason: $command->reason,
            tenantId: $command->tenantId,
        );

        Event::dispatch($adjustedEvent);

        try {
            $this->messageBroker->publish('inventory.events', $adjustedEvent->toArray());
        } catch (\Throwable $e) {
            logger()->error('[AdjustStockCommandHandler] Broker publish failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $movement->toArray();
    }
}
