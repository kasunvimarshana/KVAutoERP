<?php declare(strict_types=1);
namespace Modules\Product\Domain\RepositoryInterfaces;
use Modules\Product\Domain\Entities\ProductVariant;
interface ProductVariantRepositoryInterface {
    public function findById(int $id): ?ProductVariant;
    public function findByProduct(int $productId): array;
    public function save(ProductVariant $variant): ProductVariant;
    public function delete(int $id): void;
}
