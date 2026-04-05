<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\Batch;

interface BatchRepositoryInterface
{
    public function findById(int $id): ?Batch;

    public function findByNumber(int $tenantId, string $batchNumber): ?Batch;

    public function findByProduct(int $tenantId, int $productId, ?int $variantId, ?string $status): array;

    public function findExpiring(int $tenantId, int $days): array;

    public function create(array $data): Batch;

    public function update(int $id, array $data): ?Batch;

    public function delete(int $id): bool;
}
