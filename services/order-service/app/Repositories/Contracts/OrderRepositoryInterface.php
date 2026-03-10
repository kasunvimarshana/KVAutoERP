<?php
namespace App\Repositories\Contracts;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
interface OrderRepositoryInterface {
    public function all(array $filters = [], array $params = []): LengthAwarePaginator|Collection;
    public function findById(string $id): ?Order;
    public function findByOrderNumber(string $number): ?Order;
    public function create(array $data): Order;
    public function update(string $id, array $data): Order;
    public function delete(string $id): bool;
    public function createWithItems(array $orderData, array $items): Order;
}
