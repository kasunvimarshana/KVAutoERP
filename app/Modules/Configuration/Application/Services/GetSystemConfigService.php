<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Modules\Configuration\Application\Contracts\GetSystemConfigServiceInterface;
use Modules\Configuration\Domain\Entities\SystemConfig;
use Modules\Configuration\Domain\Repositories\SystemConfigRepositoryInterface;

class GetSystemConfigService implements GetSystemConfigServiceInterface
{
    public function __construct(
        private readonly SystemConfigRepositoryInterface $repository,
    ) {}

    public function execute(string $key, ?int $tenantId = null): ?SystemConfig
    {
        return $this->repository->findByKey($key, $tenantId);
    }
}
