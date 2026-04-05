<?php

declare(strict_types=1);

namespace Modules\Order\Application\Contracts;

use Modules\Order\Domain\Entities\OrderTransaction;

interface OrderTransactionServiceInterface
{
    public function recordPayment(
        int $orderId,
        float $amount,
        string $currency,
        string $paymentMethod,
        ?string $referenceNo = null,
    ): OrderTransaction;

    public function recordRefund(
        int $orderId,
        float $amount,
        string $currency,
        string $paymentMethod,
        ?string $referenceNo = null,
    ): OrderTransaction;
}
