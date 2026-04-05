<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\BatchLotServiceInterface;
use Modules\Inventory\Domain\Entities\BatchLot;
use Modules\Inventory\Domain\RepositoryInterfaces\BatchLotRepositoryInterface;

class BatchLotService implements BatchLotServiceInterface
{
    public function __construct(
        private readonly BatchLotRepositoryInterface $repo,
    ) {}

    public function createBatch(array $data): BatchLot
    {
        return $this->repo->create($data);
    }

    public function updateBatch(int $id, array $data): BatchLot
    {
        return $this->repo->update($id, $data);
    }

    public function findByNumber(string $batchNumber, int $tenantId): array
    {
        return $this->repo->findByNumber($batchNumber, $tenantId);
    }

    public function findExpiring(int $days, int $tenantId): array
    {
        return $this->repo->findExpiring($days, $tenantId);
    }

    public function getActive(int $tenantId): array
    {
        return $this->repo->findActive($tenantId);
    }

    public function quarantine(int $id, int $tenantId): BatchLot
    {
        $batch = $this->repo->findById($id, $tenantId);
        if ($batch === null) {
            throw new \RuntimeException("BatchLot [{$id}] not found.");
        }

        return $this->repo->update($id, ['status' => 'quarantine']);
    }

    public function consume(int $id, float $quantity, int $tenantId): BatchLot
    {
        $batch = $this->repo->findById($id, $tenantId);
        if ($batch === null) {
            throw new \RuntimeException("BatchLot [{$id}] not found.");
        }

        if ($quantity > $batch->remainingQuantity) {
            throw new \InvalidArgumentException(
                "Cannot consume {$quantity}; only {$batch->remainingQuantity} remaining."
            );
        }

        $newRemaining = $batch->remainingQuantity - $quantity;
        $newStatus    = $newRemaining <= 0.0 ? 'consumed' : $batch->status;

        return $this->repo->update($id, [
            'remaining_quantity' => $newRemaining,
            'status'             => $newStatus,
        ]);
    }
}
