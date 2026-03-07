<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->newQuery()->where('sku', $sku)->first();
    }

    public function findByCategory(string $category): Collection
    {
        return $this->newQuery()->where('category', $category)->get();
    }

    public function searchByName(string $name): Collection
    {
        return $this->newQuery()->where('name', 'LIKE', '%' . $name . '%')->get();
    }

    public function getWithPagination(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->newQuery()->paginate($perPage, ['*'], 'page', $page);
    }

    public function getLowStock(): Collection
    {
        return $this->newQuery()->whereColumn('stock_quantity', '<=', 'min_stock_level')->get();
    }
}
