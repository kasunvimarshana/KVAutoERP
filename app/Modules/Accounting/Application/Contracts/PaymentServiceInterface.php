<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Modules\Accounting\Domain\Entities\Payment;

interface PaymentServiceInterface
{
    public function findById(int $id): Payment;

    /** @return Payment[] */
    public function findByParty(string $partyType, int $partyId): array;

    public function create(array $data): Payment;

    public function update(int $id, array $data): Payment;
}
