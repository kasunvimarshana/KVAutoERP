<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Modules\Accounting\Domain\Entities\Refund;

interface RefundRepositoryInterface
{
    public function findById(int $id): ?Refund;

    public function create(array $data): Refund;

    public function update(int $id, array $data): ?Refund;
}
