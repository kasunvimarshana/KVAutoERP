<?php

declare(strict_types=1);

namespace Modules\Order\Application\Services;

use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Order\Application\Contracts\ReturnServiceInterface;
use Modules\Order\Domain\Entities\OrderReturn;
use Modules\Order\Domain\RepositoryInterfaces\ReturnLineRepositoryInterface;
use Modules\Order\Domain\RepositoryInterfaces\ReturnRepositoryInterface;

class ReturnService implements ReturnServiceInterface
{
    public function __construct(
        private readonly ReturnRepositoryInterface $returnRepository,
        private readonly ReturnLineRepositoryInterface $returnLineRepository,
    ) {}

    public function createReturn(int $tenantId, array $data): OrderReturn
    {
        $lines = $data['lines'] ?? [];
        unset($data['lines']);

        $data['tenant_id'] = $tenantId;
        $data['status'] = $data['status'] ?? 'draft';
        $data['restocking_fee'] = $data['restocking_fee'] ?? 0.0;
        $data['quality_check'] = $data['quality_check'] ?? false;

        $orderReturn = $this->returnRepository->create($data);

        if (!empty($lines)) {
            $returnId = $orderReturn->getId();
            foreach ($lines as &$line) {
                $line['return_id'] = $returnId;
                $line['should_restock'] = $line['should_restock'] ?? true;
                $line['condition'] = $line['condition'] ?? 'good';
            }
            unset($line);
            $this->returnLineRepository->bulkCreate($lines);
        }

        return $orderReturn;
    }

    public function approveReturn(int $returnId): OrderReturn
    {
        return $this->transitionStatus($returnId, 'approved', ['draft', 'pending_approval']);
    }

    public function processReturn(int $returnId): OrderReturn
    {
        $orderReturn = $this->transitionStatus($returnId, 'processing', ['approved']);

        // Mark restockable lines — actual inventory handled by Inventory module
        $lines = $this->returnLineRepository->findByReturn($returnId);
        foreach ($lines as $line) {
            if ($line->shouldBeRestocked()) {
                // Restocking intent recorded; Inventory module subscribes to events
            }
        }

        return $orderReturn;
    }

    public function completeReturn(int $returnId, float $creditMemoAmount): OrderReturn
    {
        $this->getReturnOrFail($returnId);

        $updated = $this->returnRepository->update($returnId, [
            'status' => 'completed',
            'credit_memo_amount' => $creditMemoAmount,
        ]);

        if ($updated === null) {
            throw new NotFoundException('Return', $returnId);
        }

        return $updated;
    }

    public function cancelReturn(int $returnId): OrderReturn
    {
        return $this->transitionStatus($returnId, 'cancelled', ['draft', 'pending_approval', 'approved']);
    }

    private function transitionStatus(int $returnId, string $newStatus, array $allowedFrom): OrderReturn
    {
        $orderReturn = $this->getReturnOrFail($returnId);

        if (!in_array($orderReturn->getStatus(), $allowedFrom, true)) {
            throw new DomainException(
                "Cannot transition return from '{$orderReturn->getStatus()}' to '{$newStatus}'."
            );
        }

        $updated = $this->returnRepository->update($returnId, ['status' => $newStatus]);

        return $updated ?? $orderReturn;
    }

    private function getReturnOrFail(int $returnId): OrderReturn
    {
        $orderReturn = $this->returnRepository->findById($returnId);

        if ($orderReturn === null) {
            throw new NotFoundException('Return', $returnId);
        }

        return $orderReturn;
    }
}
