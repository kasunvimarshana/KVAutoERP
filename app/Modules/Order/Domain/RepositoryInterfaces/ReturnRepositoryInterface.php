<?php

declare(strict_types=1);

namespace Modules\Order\Domain\RepositoryInterfaces;

use Modules\Order\Domain\Entities\OrderReturn;

interface ReturnRepositoryInterface
{
    public function findById(int $id): ?OrderReturn;
    /** @return OrderReturn[] */
    public function findByOriginalOrder(int $orderId): array;
    /** @return OrderReturn[] */
    public function findByStatus(int $tenantId, string $status): array;
    public function create(array $data): OrderReturn;
    public function update(int $id, array $data): ?OrderReturn;
}
