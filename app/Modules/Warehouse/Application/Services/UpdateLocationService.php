<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Warehouse\Application\Contracts\UpdateLocationServiceInterface;
use Modules\Warehouse\Application\DTOs\UpdateLocationData;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;
use Modules\Warehouse\Domain\Events\LocationUpdated;
use Modules\Warehouse\Domain\Exceptions\LocationNotFoundException;
use Modules\Warehouse\Domain\Repositories\WarehouseLocationRepositoryInterface;

class UpdateLocationService implements UpdateLocationServiceInterface
{
    public function __construct(
        private readonly WarehouseLocationRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateLocationData $data): WarehouseLocation
    {
        return DB::transaction(function () use ($id, $data): WarehouseLocation {
            $existing = $this->repository->findById($id);
            if ($existing === null) {
                throw new LocationNotFoundException($id);
            }

            $updateData = array_filter([
                'name'       => $data->name,
                'code'       => $data->code,
                'type'       => $data->type,
                'barcode'    => $data->barcode,
                'capacity'   => $data->capacity,
                'is_active'  => $data->isActive,
                'updated_by' => $data->updatedBy,
            ], fn ($v) => $v !== null);

            $location = $this->repository->updateNode($id, $updateData);

            Event::dispatch(new LocationUpdated($location));

            return $location;
        });
    }
}
