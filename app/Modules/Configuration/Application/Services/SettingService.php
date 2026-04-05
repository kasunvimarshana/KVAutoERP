<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Modules\Configuration\Application\Contracts\SettingServiceInterface;
use Modules\Configuration\Domain\Entities\Setting;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;

class SettingService implements SettingServiceInterface
{
    public function __construct(
        private readonly SettingRepositoryInterface $repository,
    ) {}

    public function get(int $tenantId, string $key): ?Setting
    {
        return $this->repository->findByKey($tenantId, $key);
    }

    public function set(int $tenantId, string $key, mixed $value, string $group = 'general', string $type = 'string'): Setting
    {
        return $this->repository->set($tenantId, $key, $value, $group, $type);
    }

    public function getGroup(int $tenantId, string $group): array
    {
        return $this->repository->findByGroup($tenantId, $group);
    }

    public function getAll(int $tenantId): array
    {
        return $this->repository->allForTenant($tenantId);
    }
}
