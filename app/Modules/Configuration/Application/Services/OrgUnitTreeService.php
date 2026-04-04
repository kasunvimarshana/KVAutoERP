<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Modules\Configuration\Application\Contracts\OrgUnitTreeServiceInterface;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;

class OrgUnitTreeService implements OrgUnitTreeServiceInterface
{
    public function __construct(
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $tenantId): array
    {
        return $this->repository->buildTree($tenantId);
    }
}
