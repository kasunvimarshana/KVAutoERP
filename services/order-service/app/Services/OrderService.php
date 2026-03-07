<?php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Repositories\SagaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private readonly OrderRepository       $orderRepository,
        private readonly SagaRepository        $sagaRepository,
        private readonly OrderSagaOrchestrator $sagaOrchestrator,
    ) {}

    public function listOrders(string $tenantId, array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $repo  = $this->orderRepository->withTenant($tenantId)->withRelations(['items']);
        $query = $repo->newQuery()->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getOrder(string $tenantId, string $orderId): ?OrderDTO
    {
        $order = $this->orderRepository->withTenant($tenantId)->withRelations(['items'])->find($orderId);

        return $order ? OrderDTO::fromModel($order) : null;
    }

    public function createOrder(string $tenantId, string $userId, array $data): OrderDTO
    {
        $orderData           = $data;
        $orderData['user_id'] = $userId;

        $order = $this->sagaOrchestrator->execute($orderData, $tenantId);

        event(new OrderCreated($order));

        return OrderDTO::fromModel($order->load('items'));
    }

    public function updateOrder(string $tenantId, string $orderId, array $data): ?OrderDTO
    {
        $order = $this->orderRepository->withTenant($tenantId)->find($orderId);

        if ($order === null) {
            return null;
        }

        $updated = DB::transaction(function () use ($order, $data): Order {
            $order->fill(array_intersect_key($data, array_flip([
                'shipping_address', 'billing_address', 'notes', 'metadata',
            ])))->save();

            return $order->fresh(['items']);
        });

        return OrderDTO::fromModel($updated);
    }

    public function updateStatus(string $tenantId, string $orderId, string $status): ?OrderDTO
    {
        $order = $this->orderRepository->withTenant($tenantId)->find($orderId);

        if ($order === null) {
            return null;
        }

        $updated = DB::transaction(function () use ($order, $status): Order {
            $previousStatus = $order->status;
            $order->status  = $status;
            $order->save();

            event(new OrderStatusUpdated($order->fresh(), $previousStatus));

            return $order->fresh(['items']);
        });

        return OrderDTO::fromModel($updated);
    }

    public function cancelOrder(string $tenantId, string $orderId): ?OrderDTO
    {
        $order = $this->orderRepository->withTenant($tenantId)->withRelations(['items'])->find($orderId);

        if ($order === null) {
            return null;
        }

        if (!$order->isCancellable()) {
            throw new \RuntimeException("Order cannot be cancelled in status: {$order->status}");
        }

        $cancelled = DB::transaction(function () use ($order, $tenantId): Order {
            $order->status = 'cancelled';
            $order->save();

            // Release inventory for each item
            $inventoryClient = app(InventoryServiceClient::class);
            foreach ($order->items as $item) {
                $inventoryClient->releaseStock($item->product_id, $item->quantity, $tenantId, $order->id);
            }

            event(new OrderCancelled($order->fresh(['items'])));

            return $order->fresh(['items']);
        });

        return OrderDTO::fromModel($cancelled);
    }

    public function deleteOrder(string $tenantId, string $orderId): bool
    {
        $order = $this->orderRepository->withTenant($tenantId)->find($orderId);

        if ($order === null) {
            return false;
        }

        return DB::transaction(fn () => $this->orderRepository->delete($orderId));
    }
}
