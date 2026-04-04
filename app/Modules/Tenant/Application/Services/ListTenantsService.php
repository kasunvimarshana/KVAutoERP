<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Modules\Tenant\Application\Contracts\ListTenantsServiceInterface;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class ListTenantsService implements ListTenantsServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(int $page = 1, int $perPage = 15): array
    {
        return $this->repository->findAll($page, $perPage);
    }
}
