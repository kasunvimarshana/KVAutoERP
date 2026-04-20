<?php

declare(strict_types=1);

namespace Modules\Finance\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Finance\Application\Contracts\FindPaymentServiceInterface;
use Modules\Finance\Domain\RepositoryInterfaces\PaymentRepositoryInterface;

class FindPaymentService extends BaseService implements FindPaymentServiceInterface
{
    public function __construct(private readonly PaymentRepositoryInterface $paymentRepository)
    {
        parent::__construct($paymentRepository);
    }

    protected function handle(array $data): mixed
    {
        return null;
    }
}
