<?php

declare(strict_types=1);

namespace Modules\Order\Domain\RepositoryInterfaces;

use Modules\Order\Domain\Entities\OrderLine;

interface OrderLineRepositoryInterface
{
    /** @return OrderLine[] */
    public function findByOrder(int $orderId): array;
    public function create(array $data): OrderLine;
    public function update(int $id, array $data): ?OrderLine;
    public function delete(int $id): bool;
    /** @return OrderLine[] */
    public function bulkCreate(array $lines): array;
}
