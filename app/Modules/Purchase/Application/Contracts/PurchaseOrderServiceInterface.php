<?php

declare(strict_types=1);

namespace Modules\Purchase\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Purchase\Domain\Entities\PurchaseOrder;

interface PurchaseOrderServiceInterface
{
    public function create(array $data): PurchaseOrder;
    public function update(int $id, array $data): PurchaseOrder;
    public function delete(int $id): bool;
    public function findById(int $id): ?PurchaseOrder;
    public function findByTenant(int $tenantId): Collection;
    public function addLine(int $orderId, array $lineData): PurchaseOrder;
    public function confirm(int $id): PurchaseOrder;
    public function cancel(int $id): PurchaseOrder;
}
