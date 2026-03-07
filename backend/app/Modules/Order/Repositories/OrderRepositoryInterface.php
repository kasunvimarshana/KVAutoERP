<?php

namespace App\Modules\Order\Repositories;

use App\Modules\Order\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function findById(string $id, string $tenantId): ?Order;

    public function findByOrderNumber(string $orderNumber, string $tenantId): ?Order;

    public function paginate(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function create(array $data): Order;

    public function createWithItems(array $orderData, array $items): Order;

    public function update(Order $order, array $data): Order;

    public function delete(Order $order): bool;
}
