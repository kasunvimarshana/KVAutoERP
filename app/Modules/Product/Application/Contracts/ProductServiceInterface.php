<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Entities\ProductComponent;
use Modules\Product\Domain\Entities\ProductVariant;

interface ProductServiceInterface
{
    public function getById(int $id): Product;

    public function findBySku(int $tenantId, string $sku): ?Product;

    public function findByBarcode(int $tenantId, string $barcode): ?Product;

    public function getByTenant(int $tenantId): Collection;

    public function search(string $query, int $tenantId): Collection;

    public function create(array $data): Product;

    public function update(int $id, array $data): Product;

    public function delete(int $id): bool;

    public function addVariant(int $productId, array $data): ProductVariant;

    public function removeVariant(int $variantId): bool;

    public function addComponent(int $productId, array $data): ProductComponent;

    public function removeComponent(int $componentId): bool;
}
