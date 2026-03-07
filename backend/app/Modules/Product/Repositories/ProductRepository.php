<?php

namespace App\Modules\Product\Repositories;

use App\Core\Repository\BaseRepository;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function findByCategory(string $category): Collection
    {
        return $this->model->where('category', $category)->get();
    }
}
