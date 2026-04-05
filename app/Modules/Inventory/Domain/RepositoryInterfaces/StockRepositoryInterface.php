<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\Stock;

interface StockRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Stock;

    public function findByProductAndLocation(int $productId, ?int $variantId, int $locationId, int $tenantId): ?Stock;

    public function findByProduct(int $productId, int $tenantId): array;

    public function findByLocation(int $locationId, int $tenantId): array;

    public function allByTenant(int $tenantId): array;

    public function upsert(array $data): Stock;

    public function updateQuantity(int $id, float $quantityDelta, int $tenantId): Stock;

    public function delete(int $id, int $tenantId): bool;
}
