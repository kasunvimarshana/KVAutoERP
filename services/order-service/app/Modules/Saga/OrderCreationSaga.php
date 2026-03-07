<?php

namespace App\Modules\Saga;

use App\Modules\Order\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OrderCreationSaga implements the Saga pattern for distributed order creation.
 * 
 * Steps:
 * 1. Create order record (local)
 * 2. Reserve inventory (inventory-service)
 * 3. Confirm order
 * 
 * Compensating transactions:
 * - If step 2 fails: cancel order (step 1 compensation)
 * - If step 3 fails: release inventory (step 2 compensation) + cancel order
 */
class OrderCreationSaga
{
    private array $compensations = [];

    public function __construct(
        private readonly string $inventoryServiceUrl,
        private readonly string $serviceToken
    ) {}

    /**
     * Execute the saga to create an order with distributed inventory reservation.
     */
    public function execute(Order $order): Order
    {
        try {
            // Step 1: Reserve inventory for each item
            $this->reserveInventory($order);

            // Step 2: Confirm the order
            $order = $this->confirmOrder($order);

            Log::info('OrderCreationSaga completed successfully', ['order_id' => $order->id]);
            return $order;

        } catch (\Exception $e) {
            Log::error('OrderCreationSaga failed, executing compensations', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            $this->executeCompensations($order);
            throw $e;
        }
    }

    private function reserveInventory(Order $order): void
    {
        $reservedItems = [];

        foreach ($order->items as $item) {
            try {
                $response = Http::withToken($this->serviceToken)
                    ->post("{$this->inventoryServiceUrl}/internal/v1/inventory/product/{$item->product_id}/reserve", [
                        'quantity' => $item->quantity,
                    ]);

                if (!$response->successful()) {
                    // Compensate: release already-reserved items
                    $this->releaseReservedItems($reservedItems);

                    throw new \DomainException(
                        "Failed to reserve inventory for product {$item->product_sku} (HTTP {$response->status()}): " . $response->body()
                    );
                }

                $reservedItems[] = [
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                ];

                // Register compensation action
                $this->compensations[] = fn () => $this->releaseReservedItems([[
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                ]]);

                Log::info('Inventory reserved for item', [
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                ]);

            } catch (\Exception $e) {
                $this->releaseReservedItems($reservedItems);
                throw $e;
            }
        }

        // Update saga state
        $order->update([
            'saga_state' => array_merge($order->saga_state ?? [], [
                'inventory_reserved' => true,
                'reserved_items'     => $reservedItems,
            ]),
        ]);
    }

    private function confirmOrder(Order $order): Order
    {
        $order->update(['status' => Order::STATUS_CONFIRMED]);
        return $order->fresh();
    }

    private function releaseReservedItems(array $items): void
    {
        foreach ($items as $item) {
            try {
                Http::withToken($this->serviceToken)
                    ->post("{$this->inventoryServiceUrl}/internal/v1/inventory/product/{$item['product_id']}/release", [
                        'quantity' => $item['quantity'],
                    ]);
                Log::info('Reservation released for item', $item);
            } catch (\Exception $e) {
                Log::error('Failed to release reservation', [
                    'item'  => $item,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function executeCompensations(Order $order): void
    {
        // Execute compensations in reverse order
        foreach (array_reverse($this->compensations) as $compensation) {
            try {
                $compensation();
            } catch (\Exception $e) {
                Log::error('Compensation failed', ['error' => $e->getMessage()]);
            }
        }

        // Cancel the order
        $order->update([
            'status'     => Order::STATUS_FAILED,
            'saga_state' => array_merge($order->saga_state ?? [], ['compensated' => true]),
        ]);
    }
}
