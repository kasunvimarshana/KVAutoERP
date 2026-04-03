<?php
namespace Modules\Warehouse\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Warehouse\Application\Contracts\CreateWarehouseLocationServiceInterface;
use Modules\Warehouse\Application\DTOs\WarehouseLocationData;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;
use Modules\Warehouse\Domain\Events\WarehouseLocationCreated;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseLocationRepositoryInterface;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;

class CreateWarehouseLocationService implements CreateWarehouseLocationServiceInterface
{
    public function __construct(
        private readonly WarehouseLocationRepositoryInterface $locationRepository,
        private readonly WarehouseRepositoryInterface $warehouseRepository,
    ) {}

    public function execute(WarehouseLocationData $data): WarehouseLocation
    {
        $location = $this->locationRepository->create($data->toArray());
        $warehouse = $this->warehouseRepository->findById($location->warehouseId);
        $tenantId = $warehouse?->tenantId ?? 0;
        Event::dispatch(new WarehouseLocationCreated($tenantId, $location->id));
        return $location;
    }
}
