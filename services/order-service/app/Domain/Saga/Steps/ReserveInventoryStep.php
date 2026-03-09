<?php

declare(strict_types=1);

namespace App\Domain\Saga\Steps;

use App\Domain\Saga\Context\SagaContext;
use App\Domain\Saga\Step\AbstractSagaStep;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reserve Inventory Step.
 *
 * Saga Step 1: Reserve stock in the Inventory Service.
 *
 * Execute:    POST /api/v1/inventory/{item_id}/reserve
 * Compensate: POST /api/v1/inventory/{item_id}/release (release the reservation)
 */
class ReserveInventoryStep extends AbstractSagaStep
{
    public function __construct(
        private readonly string $inventoryServiceUrl,
        private readonly string $serviceToken,
    ) {}

    public function getName(): string
    {
        return 'reserve_inventory';
    }

    /**
     * Reserve stock for each item in the order.
     *
     * @param  SagaContext $context
     * @return void
     * @throws \RuntimeException On insufficient stock or service error
     */
    public function execute(SagaContext $context): void
    {
        $items   = $context->get('items', []);
        $orderId = $context->get('order_id');
        $tenantId = $context->get('tenant_id');

        $reservedItems = [];

        foreach ($items as $item) {
            $response = Http::timeout(30)
                ->withToken($this->serviceToken)
                ->withHeaders(['X-Tenant-ID' => $tenantId])
                ->post("{$this->inventoryServiceUrl}/api/v1/inventory/{$item['inventory_item_id']}/reserve", [
                    'quantity' => $item['quantity'],
                    'order_id' => $orderId,
                ]);

            if ($response->failed()) {
                throw new \RuntimeException(
                    "Failed to reserve inventory for item [{$item['inventory_item_id']}]: " .
                    $response->json('message', 'Unknown error'),
                );
            }

            $reservedItems[] = [
                'inventory_item_id' => $item['inventory_item_id'],
                'quantity'          => $item['quantity'],
            ];

            Log::info('Inventory reserved', [
                'item_id'  => $item['inventory_item_id'],
                'quantity' => $item['quantity'],
                'order_id' => $orderId,
            ]);
        }

        // Store reservation data in context for compensation
        $context->set('reserved_inventory', $reservedItems);
    }

    /**
     * Release all reserved inventory (compensating transaction).
     *
     * @param  SagaContext $context
     * @return void
     */
    public function compensate(SagaContext $context): void
    {
        $reservedItems = $context->get('reserved_inventory', []);
        $orderId       = $context->get('order_id');
        $tenantId      = $context->get('tenant_id');

        foreach ($reservedItems as $item) {
            try {
                Http::timeout(30)
                    ->withToken($this->serviceToken)
                    ->withHeaders(['X-Tenant-ID' => $tenantId])
                    ->post("{$this->inventoryServiceUrl}/api/v1/inventory/{$item['inventory_item_id']}/release", [
                        'quantity' => $item['quantity'],
                        'order_id' => $orderId,
                    ]);

                Log::info('Inventory reservation released (compensation)', [
                    'item_id'  => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'order_id' => $orderId,
                ]);
            } catch (\Throwable $e) {
                // Log but continue - all items must attempt compensation
                Log::error('Failed to release inventory reservation', [
                    'item_id' => $item['inventory_item_id'],
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }
}
