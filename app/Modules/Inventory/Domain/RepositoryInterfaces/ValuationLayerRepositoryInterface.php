<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\ValuationLayer;

interface ValuationLayerRepositoryInterface
{
    public function findById(int $id): ?ValuationLayer;

    public function findByProduct(int $tenantId, int $productId, ?int $variantId = null): Collection;

    public function getActiveByMethod(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        string $method,
    ): Collection;

    public function addLayer(array $data): ValuationLayer;

    public function consumeLayer(int $id, float $quantity): ValuationLayer;

    public function update(int $id, array $data): ?ValuationLayer;
}
