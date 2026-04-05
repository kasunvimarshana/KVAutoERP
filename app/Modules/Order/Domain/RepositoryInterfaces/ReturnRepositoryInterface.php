<?php declare(strict_types=1);
namespace Modules\Order\Domain\RepositoryInterfaces;
use Modules\Order\Domain\Entities\Return_;
interface ReturnRepositoryInterface {
    public function findById(int $id): ?Return_;
    public function findByOrder(int $orderId): array;
    public function save(Return_ $return): Return_;
}
