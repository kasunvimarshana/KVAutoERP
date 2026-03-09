<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Core\Abstracts\Repositories\BaseRepository;
use App\Modules\Inventory\Domain\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * ProductRepository
 *
 * Tenant-scoped product repository with inventory-specific search helpers.
 */
class ProductRepository extends BaseRepository
{
    protected string $model = Product::class;

    protected array $searchableColumns = ['name', 'sku', 'description', 'category'];

    protected array $filterableColumns = ['status', 'category', 'tenant_id'];

    protected array $sortableColumns = ['name', 'price', 'quantity', 'created_at', 'updated_at'];

    /**
     * Find product by SKU within the current tenant scope.
     */
    public function findBySku(string $sku): ?Model
    {
        return $this->findBy(['sku' => $sku]);
    }

    /**
     * Return products with stock below a threshold (low-stock alerts).
     */
    public function lowStock(
        int $threshold = 10,
        ?int $perPage = null,
        int $page = 1
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection {
        return $this->all(
            filters: ['quantity:<=' => $threshold, 'status' => 'active'],
            sort:    ['quantity' => 'asc'],
            perPage: $perPage,
            page:    $page
        );
    }
}
