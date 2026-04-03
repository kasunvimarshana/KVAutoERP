<?php
namespace Modules\Warehouse\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Warehouse\Application\Contracts\CreateWarehouseZoneServiceInterface;
use Modules\Warehouse\Application\DTOs\WarehouseZoneData;
use Modules\Warehouse\Domain\Entities\WarehouseZone;
use Modules\Warehouse\Domain\Events\WarehouseZoneCreated;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseZoneRepositoryInterface;

class CreateWarehouseZoneService implements CreateWarehouseZoneServiceInterface
{
    public function __construct(
        private readonly WarehouseZoneRepositoryInterface $zoneRepository,
        private readonly WarehouseRepositoryInterface $warehouseRepository,
    ) {}

    public function execute(WarehouseZoneData $data): WarehouseZone
    {
        $zone = $this->zoneRepository->create($data->toArray());
        $warehouse = $this->warehouseRepository->findById($zone->warehouseId);
        $tenantId = $warehouse?->tenantId ?? 0;
        Event::dispatch(new WarehouseZoneCreated($tenantId, $zone->id));
        return $zone;
    }
}
