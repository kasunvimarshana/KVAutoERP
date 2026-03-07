<?php

namespace App\Domain\Order\Repositories;

use App\Domain\Order\Entities\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(string|int $id): ?Order
    {
        return Order::find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)->first();
    }

    public function findByCustomer(string $customerId, string $tenantId): Collection
    {
        return Order::forTenant($tenantId)
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findBySagaId(string $sagaId): ?Order
    {
        return Order::where('saga_id', $sagaId)->first();
    }

    public function getByStatus(string $status, string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::forTenant($tenantId)
            ->byStatus($status)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getAllForTenant(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Order::forTenant($tenantId)->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(string|int $id, array $data): Order
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order->fresh();
    }

    public function delete(string|int $id): bool
    {
        return (bool) Order::findOrFail($id)->delete();
    }

    public function getOrderStatistics(string $tenantId): array
    {
        $base = Order::forTenant($tenantId);

        $statusCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $revenue = (clone $base)
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('total');

        $totalOrders = array_sum($statusCounts);

        $completedCount = $statusCounts[Order::STATUS_COMPLETED] ?? 0;

        return [
            'total_orders'       => $totalOrders,
            'pending'            => $statusCounts[Order::STATUS_PENDING]    ?? 0,
            'confirmed'          => $statusCounts[Order::STATUS_CONFIRMED]  ?? 0,
            'processing'         => $statusCounts[Order::STATUS_PROCESSING] ?? 0,
            'completed'          => $completedCount,
            'cancelled'          => $statusCounts[Order::STATUS_CANCELLED]  ?? 0,
            'failed'             => $statusCounts[Order::STATUS_FAILED]     ?? 0,
            'total_revenue'      => round((float) $revenue, 2),
            'average_order_value'=> $completedCount > 0 ? round((float) $revenue / $completedCount, 2) : 0,
        ];
    }
}
