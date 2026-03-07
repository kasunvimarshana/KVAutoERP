<?php

namespace App\Modules\Order\Repositories;

use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly Order $model
    ) {}

    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('items');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('order_number', 'LIKE', "%{$filters['search']}%");
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $sortField     = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $allowedSorts  = ['created_at', 'updated_at', 'total_amount', 'status'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Order
    {
        return $this->model->with('items')->find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model->with('items')->where('order_number', $orderNumber)->first();
    }

    public function findByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('items')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(OrderDTO $dto): Order
    {
        $orderNumber = 'ORD-' . strtoupper(uniqid());
        $totalAmount = 0;

        $order = $this->model->create([
            'order_number'     => $orderNumber,
            'user_id'          => $dto->userId,
            'status'           => Order::STATUS_PENDING,
            'total_amount'     => 0,
            'currency'         => $dto->currency,
            'shipping_address' => $dto->shippingAddress,
            'billing_address'  => $dto->billingAddress ?? $dto->shippingAddress,
            'notes'            => $dto->notes,
            'saga_state'       => ['created' => true],
        ]);

        foreach ($dto->items as $item) {
            $totalPrice = $item['quantity'] * $item['unit_price'];
            $totalAmount += $totalPrice;

            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $item['product_id'],
                'product_sku'  => $item['product_sku'],
                'product_name' => $item['product_name'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'total_price'  => $totalPrice,
            ]);
        }

        $order->update(['total_amount' => $totalAmount]);
        return $order->load('items');
    }

    public function updateStatus(int $id, string $status): Order
    {
        $order = $this->model->findOrFail($id);
        $order->update(['status' => $status]);
        return $order->fresh()->load('items');
    }

    public function cancel(int $id, string $reason = ''): Order
    {
        $order = $this->model->findOrFail($id);
        $order->update([
            'status'     => Order::STATUS_CANCELLED,
            'saga_state' => array_merge($order->saga_state ?? [], [
                'cancelled'        => true,
                'cancellation_reason' => $reason,
            ]),
        ]);
        return $order->fresh()->load('items');
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->destroy($id);
    }
}
