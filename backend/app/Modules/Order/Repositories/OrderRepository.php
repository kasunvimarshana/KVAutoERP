<?php

namespace App\Modules\Order\Repositories;

use App\Core\Repository\BaseRepository;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model->where('order_number', $orderNumber)->first();
    }

    public function findByUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->with('items')->get();
    }
}
