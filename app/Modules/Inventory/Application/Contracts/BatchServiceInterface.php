<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\Batch;

interface BatchServiceInterface
{
    public function createBatch(array $data): Batch;

    public function updateBatch(int $id, array $data): Batch;

    public function expireBatch(int $id): void;

    public function findExpiring(int $tenantId, int $days): array;

    public function findByProduct(int $tenantId, int $productId, ?int $variantId, ?string $status): array;
}
