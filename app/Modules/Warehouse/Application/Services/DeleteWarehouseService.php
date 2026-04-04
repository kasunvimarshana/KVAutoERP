<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Warehouse\Application\Contracts\DeleteWarehouseServiceInterface;
use Modules\Warehouse\Domain\Events\WarehouseDeleted;
use Modules\Warehouse\Domain\Exceptions\WarehouseNotFoundException;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;

class DeleteWarehouseService implements DeleteWarehouseServiceInterface
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $warehouse = $this->repository->findById($id);
            if ($warehouse === null) {
                throw new WarehouseNotFoundException($id);
            }

            $tenantId = $warehouse->tenantId;
            $this->repository->delete($id);

            Event::dispatch(new WarehouseDeleted($id, $tenantId));
        });
    }
}
