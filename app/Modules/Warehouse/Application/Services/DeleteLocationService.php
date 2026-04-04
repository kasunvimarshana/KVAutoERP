<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Warehouse\Application\Contracts\DeleteLocationServiceInterface;
use Modules\Warehouse\Domain\Events\LocationDeleted;
use Modules\Warehouse\Domain\Exceptions\LocationNotFoundException;
use Modules\Warehouse\Domain\Repositories\WarehouseLocationRepositoryInterface;

class DeleteLocationService implements DeleteLocationServiceInterface
{
    public function __construct(
        private readonly WarehouseLocationRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $location = $this->repository->findById($id);
            if ($location === null) {
                throw new LocationNotFoundException($id);
            }

            $tenantId = $location->tenantId;
            $this->repository->deleteNode($id);

            Event::dispatch(new LocationDeleted($id, $tenantId));
        });
    }
}
