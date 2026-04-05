<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Payment;

interface PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment;

    public function findByReference(int $tenantId, string $referenceNo): ?Payment;

    /**
     * Returns all payments for a polymorphic payable (e.g. invoice, order).
     *
     * @return Collection<int, Payment>
     */
    public function findByPayable(string $payableType, int $payableId): Collection;

    public function create(array $data): Payment;

    public function update(int $id, array $data): ?Payment;

    public function delete(int $id): bool;
}
