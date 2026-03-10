<?php

declare(strict_types=1);

namespace App\Application\Contracts\Services;

use App\Application\DTOs\ProductDTO;
use App\Domain\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    public function getAllProducts(array $params): LengthAwarePaginator;
    public function getProduct(int $id, string|int $tenantId): Product;
    public function createProduct(ProductDTO $dto): Product;
    public function updateProduct(int $id, array $data, string|int $tenantId): Product;
    public function deleteProduct(int $id, string|int $tenantId): bool;
    public function searchProducts(string $query, string|int $tenantId, array $params = []): LengthAwarePaginator;
    public function getProductsByCategory(int $categoryId, array $params = []): LengthAwarePaginator;
}
