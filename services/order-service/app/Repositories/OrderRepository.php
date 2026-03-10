<?php
namespace App\Repositories;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
class OrderRepository extends BaseRepository implements OrderRepositoryInterface {
    public function __construct(Order $model) { parent::__construct($model); }
    protected function searchableColumns(): array { return ['order_number','notes']; }
    protected function sortableColumns(): array { return ['order_number','status','total','created_at','updated_at']; }
    public function findByOrderNumber(string $number): ?Order { return $this->model->where('order_number', $number)->first(); }
    public function createWithItems(array $orderData, array $items): Order {
        return DB::transaction(function () use ($orderData, $items) {
            $order = $this->model->create($orderData);
            foreach ($items as $item) { $order->items()->create($item); }
            return $order->load('items');
        });
    }
}
