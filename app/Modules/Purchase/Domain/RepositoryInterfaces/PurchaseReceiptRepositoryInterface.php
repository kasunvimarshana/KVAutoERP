<?php

declare(strict_types=1);

namespace Modules\Purchase\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Purchase\Domain\Entities\PurchaseReceipt;

interface PurchaseReceiptRepositoryInterface
{
    public function findById(int $id): ?PurchaseReceipt;
    public function findByOrder(int $purchaseOrderId): Collection;
    public function create(array $data): PurchaseReceipt;
    public function delete(int $id): bool;
}
