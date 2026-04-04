<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Modules\Configuration\Application\Contracts\GetSettingGroupServiceInterface;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;

class GetSettingGroupService implements GetSettingGroupServiceInterface
{
    public function __construct(
        private readonly SettingRepositoryInterface $repository,
    ) {}

    public function execute(int $tenantId, string $group): array
    {
        return $this->repository->getGroup($tenantId, $group);
    }
}
