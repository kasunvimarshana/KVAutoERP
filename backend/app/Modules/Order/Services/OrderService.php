<?php

namespace App\Modules\Order\Services;

use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderStatusChanged;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->orderRepository->all($filters, $perPage);
    }

    public function get(int $id): Order
    {
        return $this->orderRepository->find($id);
    }

    public function create(OrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // Validate inventory availability
            foreach ($dto->items as $item) {
                $inventory = $this->inventoryRepository->findByProduct($item['product_id']);
                $available = $inventory ? ($inventory->quantity - $inventory->reserved_quantity) : 0;
                if ($available < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient inventory for product {$item['product_id']}. Available: {$available}",
                    ]);
                }
            }

            $totalAmount = 0;
            $orderItems = [];

            foreach ($dto->items as $item) {
                $totalAmount += $item['unit_price'] * $item['quantity'];
                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['unit_price'] * $item['quantity'],
                ];
            }

            $order = $this->orderRepository->create([
                'tenant_id' => $dto->tenantId,
                'user_id' => $dto->userId,
                'status' => Order::STATUS_PENDING,
                'total_amount' => $totalAmount,
                'notes' => $dto->notes,
                'metadata' => $dto->metadata,
            ]);

            foreach ($orderItems as $itemData) {
                $order->items()->create($itemData);
            }

            $order->load(['items.product', 'user']);
            event(new OrderCreated($order));

            return $order;
        });
    }

    public function updateStatus(int $id, string $status): Order
    {
        return DB::transaction(function () use ($id, $status) {
            $order = $this->orderRepository->find($id);
            $previousStatus = $order->status;
            $order = $this->orderRepository->updateStatus($id, $status);

            event(new OrderStatusChanged($order, $previousStatus));

            if ($status === Order::STATUS_CANCELLED) {
                event(new OrderCancelled($order));
            }

            return $order;
        });
    }

    public function delete(int $id): bool
    {
        return $this->orderRepository->delete($id);
    }
}
