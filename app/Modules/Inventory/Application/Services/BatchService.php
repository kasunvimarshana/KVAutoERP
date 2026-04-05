<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Inventory\Application\Contracts\BatchServiceInterface;
use Modules\Inventory\Domain\Entities\Batch;
use Modules\Inventory\Domain\RepositoryInterfaces\BatchRepositoryInterface;

class BatchService implements BatchServiceInterface
{
    public function __construct(
        private readonly BatchRepositoryInterface $repository,
    ) {}

    public function createBatch(array $data): Batch
    {
        return $this->repository->create($data);
    }

    public function updateBatch(int $id, array $data): Batch
    {
        $batch = $this->repository->update($id, $data);

        if ($batch === null) {
            throw new NotFoundException('Batch', $id);
        }

        return $batch;
    }

    public function expireBatch(int $id): void
    {
        $batch = $this->repository->findById($id);

        if ($batch === null) {
            throw new NotFoundException('Batch', $id);
        }

        $this->repository->update($id, ['status' => 'expired']);
    }

    public function findExpiring(int $tenantId, int $days): array
    {
        return $this->repository->findExpiring($tenantId, $days);
    }

    public function findByProduct(int $tenantId, int $productId, ?int $variantId, ?string $status): array
    {
        return $this->repository->findByProduct($tenantId, $productId, $variantId, $status);
    }
}
