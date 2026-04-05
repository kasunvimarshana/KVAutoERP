<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Collection;
use Modules\Accounting\Application\Contracts\RefundServiceInterface;
use Modules\Accounting\Domain\Entities\Refund;
use Modules\Accounting\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\RefundRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class RefundService implements RefundServiceInterface
{
    public function __construct(
        private readonly RefundRepositoryInterface $repository,
        private readonly PaymentRepositoryInterface $paymentRepository,
    ) {}

    public function createRefund(array $data): Refund
    {
        if (! empty($data['original_payment_id'])) {
            $payment = $this->paymentRepository->findById($data['original_payment_id']);
            if (! $payment) {
                throw new NotFoundException('Payment (original)', $data['original_payment_id']);
            }
            if ((float)$data['amount'] > $payment->getAmount()) {
                throw new DomainException('Refund amount cannot exceed the original payment amount.');
            }
        }

        $data['refund_number'] = $this->repository->nextRefundNumber($data['tenant_id']);
        $data['status']        = $data['status'] ?? 'pending';
        $data['currency']      = $data['currency'] ?? 'USD';

        return $this->repository->create($data);
    }

    public function getRefund(string $id): Refund
    {
        $refund = $this->repository->findById($id);
        if (! $refund) {
            throw new NotFoundException('Refund', $id);
        }
        return $refund;
    }

    public function getAll(string $tenantId): Collection
    {
        return $this->repository->allByTenant($tenantId);
    }
}
