<?php

namespace App\Repositories;

use App\Models\Order;
use Shared\Core\Repositories\BaseRepository;
use Shared\Core\Services\ExternalServiceClient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function model(): string
    {
        return Order::class;
    }

    /**
     * Search orders with cross-service filtering by product attributes
     */
    public function searchOrders(array $filters): LengthAwarePaginator
    {
        $this->applyCriteria();

        // Cross-service filtering: If product attributes are provided
        if (isset($filters['product_name']) || isset($filters['product_category'])) {
            $productClient = new ExternalServiceClient('http://product-service/api/v1/products');
            $products = $productClient->get('/', [
                'name' => $filters['product_name'] ?? null,
                'category_id' => $filters['product_category'] ?? null,
            ]);

            if ($products && isset($products['status']) && $products['status'] === 'success') {
                $productIds = collect($products['data']['data'])->pluck('id')->toArray();
                
                $this->query->whereHas('items', function ($q) use ($productIds) {
                    $q->whereIn('product_id', $productIds);
                });
            }
        }

        // Standard filters
        if (isset($filters['status'])) {
            $this->where('status', '=', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $this->where('customer_id', '=', $filters['customer_id']);
        }

        return $this->paginate($filters['per_page'] ?? 15);
    }
}
