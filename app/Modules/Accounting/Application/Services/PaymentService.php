<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\PaymentServiceInterface;
use Modules\Accounting\Domain\Entities\Payment;
use Modules\Accounting\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repository,
    ) {}

    public function findById(int $id): Payment
    {
        $payment = $this->repository->findById($id);

        if ($payment === null) {
            throw new NotFoundException('Payment', $id);
        }

        return $payment;
    }

    public function findByParty(string $partyType, int $partyId): array
    {
        return $this->repository->findByParty($partyType, $partyId);
    }

    public function create(array $data): Payment
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Payment
    {
        $payment = $this->repository->update($id, $data);

        if ($payment === null) {
            throw new NotFoundException('Payment', $id);
        }

        return $payment;
    }
}
