<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Modules\Configuration\Application\Contracts\OrgUnitTreeServiceInterface;
use Modules\Configuration\Domain\Repositories\OrgUnitRepositoryInterface;

class OrgUnitTreeService implements OrgUnitTreeServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function getTree(int $tenantId): array
    {
        return $this->repository->getTree($tenantId);
    }

    public function getDescendants(int $id): array
    {
        return $this->repository->getDescendants($id);
    }
}
