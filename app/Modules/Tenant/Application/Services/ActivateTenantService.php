<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Tenant\Application\Contracts\ActivateTenantServiceInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Events\TenantActivated;
use Modules\Tenant\Domain\Exceptions\TenantNotFoundException;
use Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;

class ActivateTenantService implements ActivateTenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(int $id): Tenant
    {
        return DB::transaction(function () use ($id): Tenant {
            $tenant = $this->repository->findById($id);

            if ($tenant === null) {
                throw new TenantNotFoundException($id);
            }

            $tenant = $this->repository->update($id, ['status' => 'active']);

            Event::dispatch(new TenantActivated($tenant));

            return $tenant;
        });
    }
}
