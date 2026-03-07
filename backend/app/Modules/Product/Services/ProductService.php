<?php

namespace App\Modules\Product\Services;

use App\Core\MessageBroker\MessageBrokerInterface;
use App\Core\Pagination\PaginationHelper;
use App\Core\Service\BaseService;
use App\Modules\Product\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductService extends BaseService
{
    public function __construct(
        ProductRepository $repository,
        private MessageBrokerInterface $broker
    ) {
        parent::__construct($repository);
    }

    public function index(array $params = []): array
    {
        $query = $this->repository->query();
        $this->applyFilters($query, $params);

        return PaginationHelper::paginate($query, $params);
    }

    public function store(array $data): Model
    {
        $product = $this->repository->create($data);

        $this->broker->publish('product.created', [
            'product_id' => $product->id,
            'tenant_id'  => $product->tenant_id,
            'sku'        => $product->sku,
        ]);

        return $product;
    }

    public function destroy(int $id): bool
    {
        $product = $this->repository->findByIdOrFail($id);
        $result  = $this->repository->delete($id);

        $this->broker->publish('product.deleted', [
            'product_id' => $product->id,
            'tenant_id'  => $product->tenant_id,
        ]);

        return $result;
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        if (!empty($params['search'])) {
            $query->where(function ($q) use ($params) {
                $q->where('name', 'like', "%{$params['search']}%")
                  ->orWhere('sku', 'like', "%{$params['search']}%")
                  ->orWhere('description', 'like', "%{$params['search']}%");
            });
        }

        if (!empty($params['category'])) {
            $query->where('category', $params['category']);
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', filter_var($params['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($params['min_price'])) {
            $query->where('price', '>=', $params['min_price']);
        }

        if (!empty($params['max_price'])) {
            $query->where('price', '<=', $params['max_price']);
        }
    }
}
