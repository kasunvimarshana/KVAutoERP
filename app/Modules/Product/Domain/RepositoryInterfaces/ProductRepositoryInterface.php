<?php declare(strict_types=1);
namespace Modules\Product\Domain\RepositoryInterfaces;
use Modules\Product\Domain\Entities\Product;
interface ProductRepositoryInterface {
    public function findById(int $id): ?Product;
    public function findBySku(int $tenantId, string $sku): ?Product;
    public function findByTenant(int $tenantId): array;
    public function findByType(int $tenantId, string $type): array;
    public function save(Product $product): Product;
    public function delete(int $id): void;
}
