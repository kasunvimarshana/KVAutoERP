<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Payment;

interface PaymentRepositoryInterface
{
    public function findById(string $id): ?Payment;
    public function allByTenant(string $tenantId): Collection;
    public function create(array $data): Payment;
    public function update(string $id, array $data): Payment;
    public function delete(string $id): bool;
    public function nextPaymentNumber(string $tenantId): string;
}
