<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Modules\Warehouse\Application\Contracts\GetLocationTreeServiceInterface;
use Modules\Warehouse\Domain\Repositories\WarehouseLocationRepositoryInterface;

class GetLocationTreeService implements GetLocationTreeServiceInterface
{
    public function __construct(
        private readonly WarehouseLocationRepositoryInterface $repository,
    ) {}

    public function execute(int $warehouseId): array
    {
        return $this->repository->getTree($warehouseId);
    }
}
