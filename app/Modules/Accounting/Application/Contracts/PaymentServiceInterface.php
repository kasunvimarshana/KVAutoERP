<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Payment;

interface PaymentServiceInterface
{
    public function createPayment(array $data): Payment;

    public function getPayment(int $id): Payment;

    /** @return Collection<int, Payment> */
    public function getPaymentsForPayable(string $payableType, int $payableId): Collection;

    public function voidPayment(int $id): bool;
}
