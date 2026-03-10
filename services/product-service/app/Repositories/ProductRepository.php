<?php
namespace App\Repositories;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model) { parent::__construct($model); }

    protected function searchableColumns(): array { return ['name', 'code', 'sku', 'description']; }
    protected function sortableColumns(): array { return ['name', 'code', 'price', 'created_at', 'updated_at']; }

    public function findByCode(string $code, string $tenantId): ?Product
    {
        return $this->model->where('code', $code)->where('tenant_id', $tenantId)->first();
    }

    public function findByIds(array $ids, string $tenantId): Collection
    {
        return $this->model->whereIn('id', $ids)->where('tenant_id', $tenantId)->get();
    }

    public function findByCodes(array $codes, string $tenantId): Collection
    {
        return $this->model->whereIn('code', $codes)->where('tenant_id', $tenantId)->get();
    }
}
