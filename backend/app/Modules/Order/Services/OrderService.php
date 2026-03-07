<?php

namespace App\Modules\Order\Services;

use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Repositories\OrderRepositoryInterface;
use App\Modules\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private InventoryRepositoryInterface $inventoryRepository,
    ) {}

    public function list(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->paginate($tenantId, $perPage, $filters);
    }

    public function findById(string $id, string $tenantId): Order
    {
        $order = $this->orderRepository->findById($id, $tenantId);

        if (!$order) {
            throw new \RuntimeException("Order not found: {$id}");
        }

        return $order;
    }

    public function create(OrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $orderItems = [];
            $subtotal   = 0.00;

            foreach ($dto->items as $item) {
                $product = $this->productRepository->findById($item['product_id'], $dto->tenantId);

                if (!$product) {
                    throw new \RuntimeException("Product not found: {$item['product_id']}");
                }

                $unitPrice    = (float) ($item['unit_price'] ?? $product->price);
                $qty          = (int) $item['quantity'];
                $itemDiscount = (float) ($item['discount'] ?? 0.00);
                $itemTotal    = ($unitPrice * $qty) - $itemDiscount;
                $subtotal    += $itemTotal;

                $orderItems[] = [
                    'id'           => Str::uuid()->toString(),
                    'product_id'   => $product->id,
                    'product_sku'  => $product->sku,
                    'product_name' => $product->name,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'discount'     => $itemDiscount,
                    'total'        => $itemTotal,
                ];
            }

            $total = $subtotal + $dto->tax - $dto->discount;

            $orderData = [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $dto->tenantId,
                'user_id'          => $dto->userId,
                'order_number'     => Order::generateOrderNumber($dto->tenantId),
                'status'           => Order::STATUS_PENDING,
                'subtotal'         => $subtotal,
                'tax'              => $dto->tax,
                'discount'         => $dto->discount,
                'total'            => $total,
                'currency'         => $dto->currency,
                'notes'            => $dto->notes,
                'shipping_address' => $dto->shippingAddress,
                'billing_address'  => $dto->billingAddress,
                'metadata'         => $dto->metadata,
            ];

            $order = $this->orderRepository->createWithItems($orderData, $orderItems);

            Event::dispatch(new OrderCreated($order));

            return $order;
        });
    }

    public function updateStatus(string $id, string $tenantId, string $status): Order
    {
        $order = $this->findById($id, $tenantId);

        $updateData = ['status' => $status];

        if ($status === Order::STATUS_COMPLETED) {
            $updateData['completed_at'] = now();
        } elseif ($status === Order::STATUS_CANCELLED) {
            $updateData['cancelled_at'] = now();
        }

        $order = $this->orderRepository->update($order, $updateData);

        if ($status === Order::STATUS_COMPLETED) {
            Event::dispatch(new OrderCompleted($order));
        } elseif ($status === Order::STATUS_CANCELLED) {
            Event::dispatch(new OrderCancelled($order));
        }

        return $order;
    }

    public function cancel(string $id, string $tenantId): Order
    {
        return $this->updateStatus($id, $tenantId, Order::STATUS_CANCELLED);
    }

    public function complete(string $id, string $tenantId): Order
    {
        return $this->updateStatus($id, $tenantId, Order::STATUS_COMPLETED);
    }

    public function delete(string $id, string $tenantId): bool
    {
        $order = $this->findById($id, $tenantId);

        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CANCELLED])) {
            throw new \RuntimeException("Cannot delete order in status: {$order->status}");
        }

        return $this->orderRepository->delete($order);
    }
}
