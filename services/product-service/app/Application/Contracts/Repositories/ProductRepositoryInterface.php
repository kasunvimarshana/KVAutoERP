<?php

declare(strict_types=1);

namespace App\Application\Contracts\Repositories;

use App\Domain\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Shared\BaseRepository\BaseRepositoryInterface;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code, string|int $tenantId): ?Product;
    public function findBySku(string $sku, string|int $tenantId): ?Product;
    public function findByCategory(int $categoryId, array $params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    public function findByIds(array $ids, string|int $tenantId): Collection;
    public function getProductsWithLowStock(string|int $tenantId, int $threshold = 10): Collection;
}
