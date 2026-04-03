<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
use Modules\Product\Domain\Entities\ComboItem;
use Modules\Product\Domain\RepositoryInterfaces\ComboItemRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductComboItemModel;
class EloquentComboItemRepository extends EloquentRepository implements ComboItemRepositoryInterface
{
    public function __construct(ProductComboItemModel $model) { parent::__construct($model); }
    public function findByProduct(int $productId): Collection { return new Collection(); }
    public function save(ComboItem $item): ComboItem { return $item; }
}
