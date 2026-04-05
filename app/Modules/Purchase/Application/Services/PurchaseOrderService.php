<?php

declare(strict_types=1);

namespace Modules\Purchase\Application\Services;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Purchase\Application\Contracts\PurchaseOrderServiceInterface;
use Modules\Purchase\Domain\Entities\PurchaseOrder;
use Modules\Purchase\Domain\RepositoryInterfaces\PurchaseOrderRepositoryInterface;

class PurchaseOrderService implements PurchaseOrderServiceInterface
{
    public function __construct(
        private readonly PurchaseOrderRepositoryInterface $repository,
    ) {}

    public function create(array $data): PurchaseOrder
    {
        $data['status'] = $data['status'] ?? PurchaseOrder::STATUS_DRAFT;
        $data['subtotal'] = $data['subtotal'] ?? 0;
        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $data['total'] = $data['total'] ?? 0;
        $data['exchange_rate'] = $data['exchange_rate'] ?? 1.0;
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): PurchaseOrder
    {
        $order = $this->repository->findById($id);
        if (! $order) {
            throw new NotFoundException("PurchaseOrder #{$id} not found.");
        }
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function findById(int $id): ?PurchaseOrder
    {
        return $this->repository->findById($id);
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->repository->findByTenant($tenantId);
    }

    public function addLine(int $orderId, array $lineData): PurchaseOrder
    {
        $this->repository->addLine($orderId, $lineData);
        return $this->recalculateTotals($orderId);
    }

    public function confirm(int $id): PurchaseOrder
    {
        $order = $this->repository->findById($id);
        if (! $order) {
            throw new NotFoundException("PurchaseOrder #{$id} not found.");
        }
        return $this->repository->update($id, ['status' => PurchaseOrder::STATUS_CONFIRMED]);
    }

    public function cancel(int $id): PurchaseOrder
    {
        $order = $this->repository->findById($id);
        if (! $order) {
            throw new NotFoundException("PurchaseOrder #{$id} not found.");
        }
        return $this->repository->update($id, ['status' => PurchaseOrder::STATUS_CANCELLED]);
    }

    private function recalculateTotals(int $orderId): PurchaseOrder
    {
        $lines = $this->repository->getLines($orderId);
        $subtotal = 0.0;
        $taxAmount = 0.0;
        foreach ($lines as $line) {
            $lineSubtotal = $line->getUnitPrice() * $line->getQuantity() * (1 - $line->getDiscountRate() / 100);
            $subtotal += $lineSubtotal;
            $taxAmount += $lineSubtotal * $line->getTaxRate() / 100;
        }
        return $this->repository->update($orderId, [
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'total'      => $subtotal + $taxAmount,
        ]);
    }
}
