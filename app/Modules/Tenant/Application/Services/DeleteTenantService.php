<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Tenant\Application\Contracts\DeleteTenantServiceInterface;
use Modules\Tenant\Domain\Events\TenantDeleted;
use Modules\Tenant\Domain\Exceptions\TenantNotFoundException;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class DeleteTenantService implements DeleteTenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $tenant = $this->repository->findById($id);
        if ($tenant === null) {
            throw new TenantNotFoundException($id);
        }

        Event::dispatch(new TenantDeleted($id, $id));

        $this->repository->delete($id);
    }
}
