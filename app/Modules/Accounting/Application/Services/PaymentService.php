<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Collection;
use Modules\Accounting\Application\Contracts\PaymentServiceInterface;
use Modules\Accounting\Domain\Entities\Payment;
use Modules\Accounting\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repository,
    ) {}

    public function createPayment(array $data): Payment
    {
        $data['payment_number'] = $this->repository->nextPaymentNumber($data['tenant_id']);
        $data['status']         = $data['status'] ?? 'pending';
        $data['currency']       = $data['currency'] ?? 'USD';

        return $this->repository->create($data);
    }

    public function updatePayment(string $id, array $data): Payment
    {
        $this->getPayment($id);
        return $this->repository->update($id, $data);
    }

    public function deletePayment(string $id): bool
    {
        $this->getPayment($id);
        return $this->repository->delete($id);
    }

    public function getPayment(string $id): Payment
    {
        $payment = $this->repository->findById($id);
        if (! $payment) {
            throw new NotFoundException('Payment', $id);
        }
        return $payment;
    }

    public function getAll(string $tenantId): Collection
    {
        return $this->repository->allByTenant($tenantId);
    }
}
