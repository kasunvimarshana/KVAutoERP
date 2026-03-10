<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Application\Contracts\Repositories\ProductRepositoryInterface;
use App\Domain\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Shared\BaseRepository\BaseRepository;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected array $searchableColumns = [
        'name', 'code', 'sku', 'description', 'short_description', 'barcode',
    ];

    protected array $filterableColumns = [
        'tenant_id', 'category_id', 'is_active', 'is_featured', 'price', 'unit',
    ];

    protected array $sortableColumns = [
        'name', 'code', 'price', 'cost_price', 'created_at', 'updated_at',
    ];

    protected array $defaultRelations = ['category'];

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code, string|int $tenantId): ?Product
    {
        return $this->newQuery()
            ->where('code', strtoupper($code))
            ->where('tenant_id', $tenantId)
            ->with($this->defaultRelations)
            ->first();
    }

    public function findBySku(string $sku, string|int $tenantId): ?Product
    {
        return $this->newQuery()
            ->where('sku', $sku)
            ->where('tenant_id', $tenantId)
            ->with($this->defaultRelations)
            ->first();
    }

    public function findByCategory(int $categoryId, array $params = []): LengthAwarePaginator
    {
        return $this->paginate(
            params: $params,
            additionalConditions: ['category_id' => $categoryId],
        );
    }

    public function findByIds(array $ids, string|int $tenantId): Collection
    {
        return $this->newQuery()
            ->whereIn('id', $ids)
            ->where('tenant_id', $tenantId)
            ->with($this->defaultRelations)
            ->get();
    }

    public function getProductsWithLowStock(string|int $tenantId, int $threshold = 10): Collection
    {
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with($this->defaultRelations)
            ->get();
    }
}
