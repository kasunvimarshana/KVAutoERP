<?php

declare(strict_types=1);

namespace Modules\Finance\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Finance\Application\Contracts\FindPaymentMethodServiceInterface;
use Modules\Finance\Domain\RepositoryInterfaces\PaymentMethodRepositoryInterface;

class FindPaymentMethodService extends BaseService implements FindPaymentMethodServiceInterface
{
    public function __construct(private readonly PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        parent::__construct($paymentMethodRepository);
    }

    protected function handle(array $data): mixed
    {
        return null;
    }
}
