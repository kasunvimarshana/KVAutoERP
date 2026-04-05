<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Refund;

interface RefundRepositoryInterface
{
    public function findById(int $id): ?Refund;

    /** @return Collection<int, Refund> */
    public function findByPayment(int $paymentId): Collection;

    public function create(array $data): Refund;

    public function update(int $id, array $data): ?Refund;
}
