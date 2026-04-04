<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Warehouse\Application\Contracts\MoveLocationServiceInterface;
use Modules\Warehouse\Application\DTOs\MoveLocationData;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;
use Modules\Warehouse\Domain\Events\LocationMoved;
use Modules\Warehouse\Domain\Exceptions\LocationNotFoundException;
use Modules\Warehouse\Domain\Repositories\WarehouseLocationRepositoryInterface;

class MoveLocationService implements MoveLocationServiceInterface
{
    public function __construct(
        private readonly WarehouseLocationRepositoryInterface $repository,
    ) {}

    public function execute(MoveLocationData $data): WarehouseLocation
    {
        return DB::transaction(function () use ($data): WarehouseLocation {
            $existing = $this->repository->findById($data->locationId);
            if ($existing === null) {
                throw new LocationNotFoundException($data->locationId);
            }

            $previousParentId = $existing->parentId;
            $location = $this->repository->move($data->locationId, $data->newParentId);

            Event::dispatch(new LocationMoved($location, $previousParentId));

            return $location;
        });
    }
}
