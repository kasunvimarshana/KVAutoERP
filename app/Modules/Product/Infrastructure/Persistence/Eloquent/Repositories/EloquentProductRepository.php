<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;
class EloquentProductRepository extends EloquentRepository implements ProductRepositoryInterface
{
    public function __construct(ProductModel $model) { parent::__construct($model); }
    public function findBySku(int $tenantId, string $sku): ?Product { return null; }
    public function findByTenant(int $tenantId): Collection { return new Collection(); }
    public function save(Product $product): Product { return $product; }
}
