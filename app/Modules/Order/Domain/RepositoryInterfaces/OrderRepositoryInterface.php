<?php declare(strict_types=1);
namespace Modules\Order\Domain\RepositoryInterfaces;
use Modules\Order\Domain\Entities\Order;
use Modules\Order\Domain\Entities\OrderLine;
interface OrderRepositoryInterface {
    public function findById(int $id): ?Order;
    public function findByNumber(int $tenantId, string $orderNumber): ?Order;
    public function findByTenant(int $tenantId, ?string $type = null, ?string $status = null): array;
    public function save(Order $order): Order;
    public function saveLine(OrderLine $line): OrderLine;
    public function findLinesByOrder(int $orderId): array;
    public function delete(int $id): void;
}
