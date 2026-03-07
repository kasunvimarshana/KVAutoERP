<?php

namespace App\Modules\Product\Services\Contracts;

use App\Modules\Product\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    public function getAllProducts(array $filters = []): LengthAwarePaginator;

    public function getProduct(int $id): array;

    public function createProduct(array $data): Product;

    public function updateProduct(int $id, array $data): Product;

    public function deleteProduct(int $id): bool;
}
