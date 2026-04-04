<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Tenant\Application\Contracts\CreateTenantServiceInterface;
use Modules\Tenant\Application\DTOs\CreateTenantData;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Events\TenantCreated;
use Modules\Tenant\Domain\Exceptions\TenantAlreadyExistsException;
use Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;

class CreateTenantService implements CreateTenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(CreateTenantData $data): Tenant
    {
        return DB::transaction(function () use ($data): Tenant {
            if ($this->repository->findBySlug($data->slug) !== null) {
                throw new TenantAlreadyExistsException($data->slug);
            }

            $tenant = $this->repository->create([
                'name'       => $data->name,
                'slug'       => $data->slug,
                'status'     => $data->status,
                'plan'       => $data->plan,
                'settings'   => $data->settings,
                'metadata'   => $data->metadata,
                'created_by' => $data->createdBy,
                'updated_by' => $data->createdBy,
            ]);

            Event::dispatch(new TenantCreated($tenant));

            return $tenant;
        });
    }
}
