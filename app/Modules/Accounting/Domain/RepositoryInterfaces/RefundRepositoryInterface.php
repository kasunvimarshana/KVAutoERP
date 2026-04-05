<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Refund;

interface RefundRepositoryInterface
{
    public function findById(string $id): ?Refund;
    public function allByTenant(string $tenantId): Collection;
    public function create(array $data): Refund;
    public function delete(string $id): bool;
    public function nextRefundNumber(string $tenantId): string;
}
