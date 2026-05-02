<?php

declare(strict_types=1);

namespace Modules\Payments\Domain\RepositoryInterfaces;

use Modules\Payments\Domain\Entities\Payment;

interface PaymentRepositoryInterface
{
    public function findById(string $id): ?Payment;

    /** @return Payment[] */
    public function findByTenant(string $tenantId, string $orgUnitId): array;

    /** @return Payment[] */
    public function findByInvoice(string $tenantId, string $invoiceId): array;

    public function save(Payment $payment): Payment;

    public function updateStatus(string $id, string $status): Payment;

    public function delete(string $id): void;
}
