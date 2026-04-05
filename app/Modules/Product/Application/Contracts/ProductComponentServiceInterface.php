<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\ProductComponent;

interface ProductComponentServiceInterface
{
    public function addComponent(
        int $productId,
        int $componentProductId,
        float $quantity,
        string $unit,
        ?string $notes,
    ): ProductComponent;

    public function removeComponent(int $id): bool;

    /** @return ProductComponent[] */
    public function getComponents(int $productId): array;
}
