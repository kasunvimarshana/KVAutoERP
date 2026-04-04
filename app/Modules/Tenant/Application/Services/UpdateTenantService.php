<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Tenant\Application\Contracts\UpdateTenantServiceInterface;
use Modules\Tenant\Application\DTOs\UpdateTenantData;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Events\TenantUpdated;
use Modules\Tenant\Domain\Exceptions\TenantNotFoundException;
use Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;

class UpdateTenantService implements UpdateTenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateTenantData $data): Tenant
    {
        return DB::transaction(function () use ($id, $data): Tenant {
            if ($this->repository->findById($id) === null) {
                throw new TenantNotFoundException($id);
            }

            $payload = array_filter([
                'name'       => $data->name,
                'slug'       => $data->slug,
                'status'     => $data->status,
                'plan'       => $data->plan,
                'settings'   => $data->settings,
                'metadata'   => $data->metadata,
                'updated_by' => $data->updatedBy,
            ], fn ($v) => $v !== null);

            $tenant = $this->repository->update($id, $payload);

            Event::dispatch(new TenantUpdated($tenant));

            return $tenant;
        });
    }
}
