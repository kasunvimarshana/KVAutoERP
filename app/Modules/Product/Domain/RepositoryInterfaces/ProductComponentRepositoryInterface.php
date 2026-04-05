<?php declare(strict_types=1);
namespace Modules\Product\Domain\RepositoryInterfaces;
use Modules\Product\Domain\Entities\ProductComponent;
interface ProductComponentRepositoryInterface {
    public function findByParent(int $parentProductId): array;
    public function save(ProductComponent $component): ProductComponent;
    public function delete(int $id): void;
}
