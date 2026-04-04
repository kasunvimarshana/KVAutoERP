<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Warehouse\Application\Contracts\CreateLocationServiceInterface;
use Modules\Warehouse\Application\DTOs\CreateLocationData;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;
use Modules\Warehouse\Domain\Events\LocationCreated;
use Modules\Warehouse\Domain\Repositories\WarehouseLocationRepositoryInterface;

class CreateLocationService implements CreateLocationServiceInterface
{
    public function __construct(
        private readonly WarehouseLocationRepositoryInterface $repository,
    ) {}

    public function execute(CreateLocationData $data): WarehouseLocation
    {
        return DB::transaction(function () use ($data): WarehouseLocation {
            $location = $this->repository->insertNode([
                'tenant_id'    => $data->tenantId,
                'warehouse_id' => $data->warehouseId,
                'name'         => $data->name,
                'code'         => $data->code,
                'type'         => $data->type,
                'barcode'      => $data->barcode,
                'capacity'     => $data->capacity,
                'is_active'    => $data->isActive,
                'created_by'   => $data->createdBy,
                'updated_by'   => $data->createdBy,
            ], $data->parentId);

            Event::dispatch(new LocationCreated($location));

            return $location;
        });
    }
}
