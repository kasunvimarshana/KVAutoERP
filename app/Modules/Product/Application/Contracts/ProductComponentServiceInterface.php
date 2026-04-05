<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\ProductComponent;

interface ProductComponentServiceInterface
{
    public function addComponent(array $data): ProductComponent;

    public function updateComponent(int $id, array $data): ProductComponent;

    public function removeComponent(int $id, int $tenantId): bool;

    public function getComponents(int $productId, int $tenantId): array;

    public function clearComponents(int $productId, int $tenantId): void;
}
