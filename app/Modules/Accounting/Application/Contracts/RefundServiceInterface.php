<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Refund;

interface RefundServiceInterface
{
    public function createRefund(array $data): Refund;
    public function getRefund(string $id): Refund;
    public function getAll(string $tenantId): Collection;
}
