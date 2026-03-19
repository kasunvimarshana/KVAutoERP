<?php

namespace App\Repositories;

use App\Models\Product;
use Shared\Core\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository
{
    public function model(): string
    {
        return Product::class;
    }

    /**
     * Find products by category
     *
     * @param int $categoryId
     * @return Collection
     */
    public function findByCategory(int $categoryId)
    {
        return $this->findWhere(['category_id' => $categoryId]);
    }

    /**
     * Search products with filtering
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function searchProducts(array $filters)
    {
        if (isset($filters['q'])) {
            $this->search($filters['q'], ['name', 'sku', 'description', 'barcode']);
        }

        if (isset($filters['category_id'])) {
            $this->where('category_id', '=', $filters['category_id']);
        }

        if (isset($filters['type'])) {
            $this->where('type', '=', $filters['type']);
        }

        if (isset($filters['status'])) {
            $this->where('status', '=', $filters['status']);
        }

        return $this->paginate($filters['per_page'] ?? 15);
    }
}
