<?php

namespace App\Modules\Order\Repositories;

use App\Modules\Order\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function findById(string $id, string $tenantId): ?Order
    {
        return Order::with('items')->where('id', $id)->where('tenant_id', $tenantId)->first();
    }

    public function findByOrderNumber(string $orderNumber, string $tenantId): ?Order
    {
        return Order::where('order_number', $orderNumber)->where('tenant_id', $tenantId)->first();
    }

    public function paginate(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Order::with('items')->where('tenant_id', $tenantId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function createWithItems(array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($orderData, $items) {
            $order = Order::create($orderData);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            return $order->load('items');
        });
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh(['items']);
    }

    public function delete(Order $order): bool
    {
        return (bool) $order->delete();
    }
}
