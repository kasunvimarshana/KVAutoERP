<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Modules\Accounting\Domain\Entities\Refund;

interface RefundServiceInterface
{
    public function findById(int $id): Refund;

    public function create(array $data): Refund;

    public function update(int $id, array $data): Refund;
}
