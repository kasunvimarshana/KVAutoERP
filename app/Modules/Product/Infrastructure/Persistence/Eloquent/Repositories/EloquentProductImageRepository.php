<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
use Modules\Product\Domain\Entities\ProductImage;
use Modules\Product\Domain\RepositoryInterfaces\ProductImageRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductImageModel;
class EloquentProductImageRepository extends EloquentRepository implements ProductImageRepositoryInterface {
    public function __construct(ProductImageModel $model) { parent::__construct($model); }
    public function findByUuid(string $uuid): ?ProductImage { return null; }
    public function findByProduct(int $productId): Collection { return new Collection(); }
    public function getByProduct(int $productId): Collection { return new Collection(); }
    public function save(ProductImage $image): ProductImage { return $image; }
    public function deleteByProduct(int $productId): bool { return true; }
}
