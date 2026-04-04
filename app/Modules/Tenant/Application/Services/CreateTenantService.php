<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Tenant\Application\Contracts\CreateTenantServiceInterface;
use Modules\Tenant\Application\DTOs\CreateTenantData;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Events\TenantCreated;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Domain\ValueObjects\TenantPlan;

class CreateTenantService implements CreateTenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(CreateTenantData $data): Tenant
    {
        TenantPlan::assertValid($data->plan);

        $tenant = new Tenant(
            id: null,
            name: $data->name,
            slug: $data->slug,
            domain: null,
            database: null,
            status: 'active',
            plan: $data->plan,
            locale: $data->locale,
            timezone: $data->timezone,
            currency: $data->currency,
            settings: [],
            trialEndsAt: null,
            suspendedAt: null,
            createdAt: null,
            updatedAt: null,
        );

        $saved = $this->repository->save($tenant);

        Event::dispatch(new TenantCreated($saved->id, $saved->id));

        return $saved;
    }
}
