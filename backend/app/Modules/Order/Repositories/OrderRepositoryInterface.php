<?php

namespace App\Modules\Order\Repositories;

interface OrderRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15);
    public function find(int $id);
    public function create(array $data): \App\Modules\Order\Models\Order;
    public function update(int $id, array $data): \App\Modules\Order\Models\Order;
    public function delete(int $id): bool;
    public function updateStatus(int $id, string $status): \App\Modules\Order\Models\Order;
}
