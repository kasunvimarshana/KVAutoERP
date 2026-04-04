<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Warehouse\Application\Contracts\UpdateWarehouseServiceInterface;
use Modules\Warehouse\Application\DTOs\UpdateWarehouseData;
use Modules\Warehouse\Domain\Entities\Warehouse;
use Modules\Warehouse\Domain\Events\WarehouseUpdated;
use Modules\Warehouse\Domain\Exceptions\WarehouseNotFoundException;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;

class UpdateWarehouseService implements UpdateWarehouseServiceInterface
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateWarehouseData $data): Warehouse
    {
        return DB::transaction(function () use ($id, $data): Warehouse {
            $existing = $this->repository->findById($id);
            if ($existing === null) {
                throw new WarehouseNotFoundException($id);
            }

            $updateData = array_filter([
                'name'            => $data->name,
                'code'            => $data->code,
                'type'            => $data->type,
                'address'         => $data->address,
                'is_active'       => $data->isActive,
                'manager_user_id' => $data->managerUserId,
                'updated_by'      => $data->updatedBy,
            ], fn ($v) => $v !== null);

            $warehouse = $this->repository->update($id, $updateData);

            Event::dispatch(new WarehouseUpdated($warehouse));

            return $warehouse;
        });
    }
}
