<?php

declare(strict_types=1);

namespace Modules\Order\Application\Contracts;

use Modules\Order\Domain\Entities\Order;

interface OrderServiceInterface
{
    public function create(int $tenantId, array $data): Order;
    public function confirm(int $orderId): Order;
    public function process(int $orderId): Order;
    public function complete(int $orderId): Order;
    public function cancel(int $orderId): Order;
    public function findById(int $id): Order;
    public function findByNumber(int $tenantId, string $orderNumber): Order;
    /** @return Order[] */
    public function findByStatus(int $tenantId, string $status): array;
    /** @return Order[] */
    public function findByType(int $tenantId, string $type): array;
}
