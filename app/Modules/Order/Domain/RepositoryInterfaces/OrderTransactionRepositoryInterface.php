<?php

declare(strict_types=1);

namespace Modules\Order\Domain\RepositoryInterfaces;

use Modules\Order\Domain\Entities\OrderTransaction;

interface OrderTransactionRepositoryInterface
{
    /** @return OrderTransaction[] */
    public function findByOrder(int $orderId): array;
    public function create(array $data): OrderTransaction;
}
