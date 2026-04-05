<?php

declare(strict_types=1);

namespace Modules\Order\Application\Services;

use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Order\Application\Contracts\OrderServiceInterface;
use Modules\Order\Domain\Entities\Order;
use Modules\Order\Domain\RepositoryInterfaces\OrderLineRepositoryInterface;
use Modules\Order\Domain\RepositoryInterfaces\OrderRepositoryInterface;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderLineRepositoryInterface $lineRepository,
    ) {}

    public function create(int $tenantId, array $data): Order
    {
        $lines = $data['lines'] ?? [];
        unset($data['lines']);

        $data['tenant_id'] = $tenantId;
        $data['status'] = $data['status'] ?? 'draft';

        if (!empty($lines)) {
            $subtotal = 0.0;
            foreach ($lines as &$line) {
                $lineTotal = ((float) ($line['quantity'] ?? 0))
                    * ((float) ($line['unit_price'] ?? 0))
                    - ((float) ($line['discount_amount'] ?? 0))
                    + ((float) ($line['tax_amount'] ?? 0));
                $line['total_amount'] = $lineTotal;
                $subtotal += $lineTotal;
            }
            unset($line);

            $data['subtotal'] = $subtotal;
            $data['total_amount'] = $this->calculateTotal(
                $subtotal,
                (float) ($data['discount_amount'] ?? 0),
                (float) ($data['tax_amount'] ?? 0),
                (float) ($data['shipping_amount'] ?? 0),
            );
        }

        $order = $this->orderRepository->create($data);

        if (!empty($lines)) {
            $orderId = $order->getId();
            foreach ($lines as &$line) {
                $line['order_id'] = $orderId;
            }
            unset($line);
            $this->lineRepository->bulkCreate($lines);
        }

        return $order;
    }

    public function confirm(int $orderId): Order
    {
        return $this->transitionStatus($orderId, 'confirmed', ['draft']);
    }

    public function process(int $orderId): Order
    {
        return $this->transitionStatus($orderId, 'processing', ['confirmed']);
    }

    public function complete(int $orderId): Order
    {
        return $this->transitionStatus($orderId, 'completed', ['processing', 'shipped', 'delivered']);
    }

    public function cancel(int $orderId): Order
    {
        $order = $this->findById($orderId);

        if (!$order->canBeCancelled()) {
            throw new DomainException("Order '{$order->getOrderNumber()}' cannot be cancelled in status '{$order->getStatus()}'.");
        }

        $updated = $this->orderRepository->updateStatus($orderId, 'cancelled');

        return $updated ?? $order;
    }

    public function findById(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if ($order === null) {
            throw new NotFoundException('Order', $id);
        }

        return $order;
    }

    public function findByNumber(int $tenantId, string $orderNumber): Order
    {
        $order = $this->orderRepository->findByNumber($tenantId, $orderNumber);

        if ($order === null) {
            throw new NotFoundException("Order with number '{$orderNumber}'");
        }

        return $order;
    }

    public function findByStatus(int $tenantId, string $status): array
    {
        return $this->orderRepository->findByStatus($tenantId, $status);
    }

    public function findByType(int $tenantId, string $type): array
    {
        return $this->orderRepository->findByType($tenantId, $type);
    }

    private function transitionStatus(int $orderId, string $newStatus, array $allowedFrom): Order
    {
        $order = $this->findById($orderId);

        if (!in_array($order->getStatus(), $allowedFrom, true)) {
            throw new DomainException(
                "Cannot transition order from '{$order->getStatus()}' to '{$newStatus}'."
            );
        }

        $updated = $this->orderRepository->updateStatus($orderId, $newStatus);

        return $updated ?? $order;
    }

    private function calculateTotal(float $subtotal, float $discount, float $tax, float $shipping): float
    {
        return $subtotal - $discount + $tax + $shipping;
    }
}
