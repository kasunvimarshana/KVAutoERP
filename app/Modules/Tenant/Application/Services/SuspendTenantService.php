<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Tenant\Application\Contracts\SuspendTenantServiceInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Events\TenantSuspended;
use Modules\Tenant\Domain\Exceptions\TenantNotFoundException;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Domain\ValueObjects\TenantStatus;

class SuspendTenantService implements SuspendTenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(int $id): Tenant
    {
        $tenant = $this->repository->findById($id);
        if ($tenant === null) {
            throw new TenantNotFoundException($id);
        }

        $tenant->status = TenantStatus::SUSPENDED;
        $tenant->suspendedAt = new \DateTimeImmutable();

        $saved = $this->repository->save($tenant);

        Event::dispatch(new TenantSuspended($saved->id, $saved->id));

        return $saved;
    }
}
