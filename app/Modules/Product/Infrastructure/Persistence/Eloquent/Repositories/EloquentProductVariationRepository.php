<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
use Modules\Product\Domain\Entities\ProductVariation;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariationRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductVariationModel;
class EloquentProductVariationRepository extends EloquentRepository implements ProductVariationRepositoryInterface
{
    public function __construct(ProductVariationModel $model) { parent::__construct($model); }
    public function findByProduct(int $productId): Collection { return new Collection(); }
    public function save(ProductVariation $variation): ProductVariation { return $variation; }
}
