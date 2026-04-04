<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Warehouse\Application\Contracts\CreateWarehouseServiceInterface;
use Modules\Warehouse\Application\DTOs\CreateWarehouseData;
use Modules\Warehouse\Domain\Entities\Warehouse;
use Modules\Warehouse\Domain\Events\WarehouseCreated;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;

class CreateWarehouseService implements CreateWarehouseServiceInterface
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
    ) {}

    public function execute(CreateWarehouseData $data): Warehouse
    {
        return DB::transaction(function () use ($data): Warehouse {
            $warehouse = $this->repository->create([
                'tenant_id'       => $data->tenantId,
                'name'            => $data->name,
                'code'            => $data->code,
                'type'            => $data->type,
                'address'         => $data->address,
                'is_active'       => $data->isActive,
                'manager_user_id' => $data->managerUserId,
                'created_by'      => $data->createdBy,
                'updated_by'      => $data->createdBy,
            ]);

            Event::dispatch(new WarehouseCreated($warehouse));

            return $warehouse;
        });
    }
}
