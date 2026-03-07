<?php

namespace App\Modules\Order\Repositories\Interfaces;

use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    public function findByUserId(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function create(OrderDTO $dto): Order;

    public function updateStatus(int $id, string $status): Order;

    public function cancel(int $id, string $reason): Order;

    public function delete(int $id): bool;
}
