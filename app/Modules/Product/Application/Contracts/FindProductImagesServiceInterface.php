<?php declare(strict_types=1);
namespace Modules\Product\Application\Contracts;
use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\ProductImage;
interface FindProductImagesServiceInterface {
    public function findByProduct(int $productId): Collection;
    public function findByUuid(string $uuid): ?ProductImage;
}
