<?php

declare(strict_types=1);

namespace Modules\Order\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Order\Application\Contracts\OrderTransactionServiceInterface;
use Modules\Order\Domain\Entities\OrderTransaction;
use Modules\Order\Domain\RepositoryInterfaces\OrderRepositoryInterface;
use Modules\Order\Domain\RepositoryInterfaces\OrderTransactionRepositoryInterface;

class OrderTransactionService implements OrderTransactionServiceInterface
{
    public function __construct(
        private readonly OrderTransactionRepositoryInterface $transactionRepository,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function recordPayment(
        int $orderId,
        float $amount,
        string $currency,
        string $paymentMethod,
        ?string $referenceNo = null,
    ): OrderTransaction {
        return $this->record($orderId, 'payment', $amount, $currency, $paymentMethod, $referenceNo);
    }

    public function recordRefund(
        int $orderId,
        float $amount,
        string $currency,
        string $paymentMethod,
        ?string $referenceNo = null,
    ): OrderTransaction {
        return $this->record($orderId, 'refund', $amount, $currency, $paymentMethod, $referenceNo);
    }

    private function record(
        int $orderId,
        string $type,
        float $amount,
        string $currency,
        string $paymentMethod,
        ?string $referenceNo,
    ): OrderTransaction {
        $order = $this->orderRepository->findById($orderId);

        if ($order === null) {
            throw new NotFoundException('Order', $orderId);
        }

        return $this->transactionRepository->create([
            'tenant_id'      => $order->getTenantId(),
            'order_id'       => $orderId,
            'type'           => $type,
            'amount'         => $amount,
            'currency'       => $currency,
            'payment_method' => $paymentMethod,
            'status'         => 'completed',
            'reference_no'   => $referenceNo,
        ]);
    }
}
