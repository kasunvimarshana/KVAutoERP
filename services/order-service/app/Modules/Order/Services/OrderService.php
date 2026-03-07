<?php

namespace App\Modules\Order\Services;

use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Repositories\Interfaces\OrderRepositoryInterface;
use App\Modules\Saga\OrderCreationSaga;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    public function listOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->findAll($filters, $perPage);
    }

    public function getOrder(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Order with ID {$id} not found."
            );
        }

        return $order;
    }

    /**
     * Create an order using the Saga pattern for distributed transaction management.
     * Coordinates with Inventory Service to reserve stock.
     */
    public function createOrder(OrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // Step 1: Create the order record
            $order = $this->orderRepository->create($dto);

            Log::info('Order created, starting saga', ['order_id' => $order->id]);

            // Step 2: Execute the OrderCreationSaga
            $saga = new OrderCreationSaga(
                inventoryServiceUrl: config('services.inventory.url'),
                serviceToken:        $this->getServiceToken()
            );

            try {
                $order = $saga->execute($order);
                Event::dispatch(new OrderCreated($order));
                return $order;

            } catch (\Exception $e) {
                Log::error('Order saga failed', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Cancel an order with compensating transactions.
     */
    public function cancelOrder(int $id, string $reason = ''): Order
    {
        return DB::transaction(function () use ($id, $reason) {
            $order = $this->orderRepository->findById($id);

            if (!$order) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    "Order with ID {$id} not found."
                );
            }

            if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])) {
                throw new \DomainException(
                    "Cannot cancel order with status '{$order->status}'."
                );
            }

            // Release inventory reservations (compensating transaction)
            $this->releaseInventoryReservations($order);

            $order = $this->orderRepository->cancel($id, $reason);
            Event::dispatch(new OrderCancelled($order, $reason));

            Log::info('Order cancelled', ['order_id' => $id]);
            return $order;
        });
    }

    public function updateOrderStatus(int $id, string $status): Order
    {
        return DB::transaction(function () use ($id, $status) {
            return $this->orderRepository->updateStatus($id, $status);
        });
    }

    /**
     * Release inventory reservations for an order (compensating transaction).
     */
    private function releaseInventoryReservations(Order $order): void
    {
        $sagaState = $order->saga_state ?? [];

        if (empty($sagaState['inventory_reserved'])) {
            return;
        }

        $reservedItems = $sagaState['reserved_items'] ?? [];

        foreach ($reservedItems as $item) {
            try {
                $serviceToken = $this->getServiceToken();
                $inventoryUrl = config('services.inventory.url');

                \Illuminate\Support\Facades\Http::withToken($serviceToken)
                    ->post("{$inventoryUrl}/internal/v1/inventory/product/{$item['product_id']}/release", [
                        'quantity' => $item['quantity'],
                    ]);

                Log::info('Inventory reservation released', $item);
            } catch (\Exception $e) {
                Log::error('Failed to release inventory reservation', [
                    'item'  => $item,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function getServiceToken(): string
    {
        // In a real implementation, this would fetch a service-to-service token from Keycloak
        // using the client credentials flow
        return config('keycloak.service_token', '');
    }
}
