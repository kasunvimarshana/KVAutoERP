<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Collection;
use Modules\Accounting\Application\Contracts\PaymentServiceInterface;
use Modules\Accounting\Domain\Entities\Payment;
use Modules\Accounting\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

final class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repository,
    ) {}

    public function createPayment(array $data): Payment
    {
        return $this->repository->create($data);
    }

    public function getPayment(int $id): Payment
    {
        $payment = $this->repository->findById($id);

        if ($payment === null) {
            throw new NotFoundException("Payment #{$id} not found.");
        }

        return $payment;
    }

    public function getPaymentsForPayable(string $payableType, int $payableId): Collection
    {
        return $this->repository->findByPayable($payableType, $payableId);
    }

    public function voidPayment(int $id): bool
    {
        $payment = $this->repository->findById($id);

        if ($payment === null) {
            throw new NotFoundException("Payment #{$id} not found.");
        }

        return $this->repository->delete($id);
    }
}
