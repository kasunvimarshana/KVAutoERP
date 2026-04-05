<?php

declare(strict_types=1);

namespace Modules\Order\Domain\RepositoryInterfaces;

use Modules\Order\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;
    public function findByNumber(int $tenantId, string $orderNumber): ?Order;
    /** @return Order[] */
    public function findByStatus(int $tenantId, string $status): array;
    /** @return Order[] */
    public function findByContact(int $tenantId, int $contactId): array;
    /** @return Order[] */
    public function findByType(int $tenantId, string $type): array;
    public function create(array $data): Order;
    public function update(int $id, array $data): ?Order;
    public function delete(int $id): bool;
    public function updateStatus(int $id, string $status): ?Order;
}
