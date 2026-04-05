<?php

declare(strict_types=1);

namespace Modules\Purchase\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Purchase\Domain\Entities\PurchaseOrder;
use Modules\Purchase\Domain\Entities\PurchaseOrderLine;

interface PurchaseOrderRepositoryInterface
{
    public function findById(int $id): ?PurchaseOrder;
    public function findByTenant(int $tenantId): Collection;
    public function findByStatus(int $tenantId, string $status): Collection;
    public function create(array $data): PurchaseOrder;
    public function update(int $id, array $data): PurchaseOrder;
    public function delete(int $id): bool;
    public function addLine(int $orderId, array $data): PurchaseOrderLine;
    public function updateLine(int $lineId, array $data): PurchaseOrderLine;
    public function removeLine(int $lineId): bool;
    public function getLines(int $orderId): Collection;
}
