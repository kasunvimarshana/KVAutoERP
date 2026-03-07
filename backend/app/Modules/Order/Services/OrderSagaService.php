<?php

namespace App\Modules\Order\Services;

use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Implements the Saga pattern for distributed order processing.
 * Each step either succeeds or triggers a compensation action.
 */
class OrderSagaService
{
    private array $compensations = [];

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private InventoryRepositoryInterface $inventoryRepository,
    ) {}

    public function processOrder(Order $order): bool
    {
        $this->compensations = [];

        try {
            $this->step1_validateInventory($order);
            $this->step2_reserveInventory($order);
            $this->step3_confirmOrder($order);
            $this->step4_processPayment($order);
            $this->step5_completeOrder($order);

            return true;
        } catch (\Exception $e) {
            Log::error('Order saga failed', [
                'order_id' => $order->id,
                'step'     => $e->getMessage(),
            ]);

            $this->compensate($order);

            return false;
        }
    }

    private function step1_validateInventory(Order $order): void
    {
        foreach ($order->items as $item) {
            $inventory = $this->inventoryRepository->findByProduct($item->product_id, $order->tenant_id);

            if (!$inventory) {
                throw new \RuntimeException("No inventory record for product: {$item->product_id}");
            }

            if ($inventory->available_quantity < $item->quantity) {
                throw new \RuntimeException("Insufficient stock for product: {$item->product_sku}");
            }
        }
    }

    private function step2_reserveInventory(Order $order): void
    {
        foreach ($order->items as $item) {
            $inventory = $this->inventoryRepository->findByProduct($item->product_id, $order->tenant_id);
            $reserved  = $this->inventoryRepository->reserveQuantity($inventory, $item->quantity);

            if (!$reserved) {
                throw new \RuntimeException("Failed to reserve inventory for: {$item->product_sku}");
            }

            $this->compensations[] = function () use ($inventory, $item) {
                $this->inventoryRepository->releaseReservation($inventory, $item->quantity);
            };
        }
    }

    private function step3_confirmOrder(Order $order): void
    {
        $this->orderRepository->update($order, ['status' => Order::STATUS_CONFIRMED]);

        $this->compensations[] = function () use ($order) {
            $this->orderRepository->update($order, ['status' => Order::STATUS_PENDING]);
        };
    }

    private function step4_processPayment(Order $order): void
    {
        // In a real system, integrate with a payment gateway here.
        // For now, we simulate payment processing.
        Log::info('Payment processing (simulated)', ['order_id' => $order->id, 'total' => $order->total]);
    }

    private function step5_completeOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $inventory = $this->inventoryRepository->findByProduct($item->product_id, $order->tenant_id);
                $this->inventoryRepository->adjustQuantity($inventory, -$item->quantity);
                $this->inventoryRepository->releaseReservation($inventory, $item->quantity);
            }

            $this->orderRepository->update($order, [
                'status' => Order::STATUS_PROCESSING,
            ]);
        });
    }

    private function compensate(Order $order): void
    {
        foreach (array_reverse($this->compensations) as $compensation) {
            try {
                $compensation();
            } catch (\Exception $e) {
                Log::error('Saga compensation failed', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        $this->orderRepository->update($order, [
            'status'       => Order::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }
}
