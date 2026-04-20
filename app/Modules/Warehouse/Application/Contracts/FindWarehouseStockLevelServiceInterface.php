<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Core\Application\Contracts\ServiceInterface;

interface FindWarehouseStockLevelServiceInterface extends ServiceInterface
{
	public function listByWarehouse(int $tenantId, int $warehouseId, int $perPage = 15, int $page = 1): mixed;
}
