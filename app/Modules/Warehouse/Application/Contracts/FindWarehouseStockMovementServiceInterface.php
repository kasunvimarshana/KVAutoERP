<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

use Modules\Core\Application\Contracts\ServiceInterface;

interface FindWarehouseStockMovementServiceInterface extends ServiceInterface
{
	public function listByWarehouse(
		int $tenantId,
		int $warehouseId,
		array $filters = [],
		int $perPage = 15,
		int $page = 1,
		?string $sort = null,
	): mixed;
}
