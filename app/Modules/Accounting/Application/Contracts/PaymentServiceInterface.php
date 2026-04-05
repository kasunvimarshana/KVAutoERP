<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Payment;

interface PaymentServiceInterface
{
    public function createPayment(array $data): Payment;
    public function updatePayment(string $id, array $data): Payment;
    public function deletePayment(string $id): bool;
    public function getPayment(string $id): Payment;
    public function getAll(string $tenantId): Collection;
}
