<?php
declare(strict_types=1);
namespace Modules\Product\Domain\RepositoryInterfaces;
use Modules\Core\Domain\Contracts\Repositories\RepositoryInterface;
use Modules\Product\Domain\Entities\ProductVariation;
interface ProductVariationRepositoryInterface extends RepositoryInterface
{
    public function findByProduct(int $productId): \Illuminate\Support\Collection;
    public function save(ProductVariation $variation): ProductVariation;
}
