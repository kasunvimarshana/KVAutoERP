<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Tenant\Application\Contracts\UpdateTenantServiceInterface;
use Modules\Tenant\Application\DTOs\UpdateTenantData;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Events\TenantUpdated;
use Modules\Tenant\Domain\Exceptions\TenantNotFoundException;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class UpdateTenantService implements UpdateTenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateTenantData $data): Tenant
    {
        $tenant = $this->repository->findById($id);
        if ($tenant === null) {
            throw new TenantNotFoundException($id);
        }

        if ($data->name !== null) {
            $tenant->name = $data->name;
        }
        if ($data->slug !== null) {
            $tenant->slug = $data->slug;
        }
        if ($data->plan !== null) {
            $tenant->plan = $data->plan;
        }
        if ($data->locale !== null) {
            $tenant->locale = $data->locale;
        }
        if ($data->timezone !== null) {
            $tenant->timezone = $data->timezone;
        }
        if ($data->currency !== null) {
            $tenant->currency = $data->currency;
        }
        if ($data->status !== null) {
            $tenant->status = $data->status;
        }

        $saved = $this->repository->save($tenant);

        Event::dispatch(new TenantUpdated($saved->id, $saved->id));

        return $saved;
    }
}
