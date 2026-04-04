<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Modules\Tenant\Application\Contracts\GetTenantServiceInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Exceptions\TenantNotFoundException;
use Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;

class GetTenantService implements GetTenantServiceInterface
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

        return $tenant;
    }
}
