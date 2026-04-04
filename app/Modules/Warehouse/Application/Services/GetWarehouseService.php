<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Modules\Warehouse\Application\Contracts\GetWarehouseServiceInterface;
use Modules\Warehouse\Domain\Entities\Warehouse;
use Modules\Warehouse\Domain\Exceptions\WarehouseNotFoundException;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;

class GetWarehouseService implements GetWarehouseServiceInterface
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
    ) {}

    public function execute(int $id): Warehouse
    {
        $warehouse = $this->repository->findById($id);
        if ($warehouse === null) {
            throw new WarehouseNotFoundException($id);
        }

        return $warehouse;
    }
}
