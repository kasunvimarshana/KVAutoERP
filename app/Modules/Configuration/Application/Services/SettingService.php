<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Collection;
use Modules\Configuration\Application\Contracts\SettingServiceInterface;
use Modules\Configuration\Domain\Entities\Setting;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;

class SettingService implements SettingServiceInterface
{
    public function __construct(
        private readonly SettingRepositoryInterface $repository,
    ) {}

    public function get(string $key, string $tenantId): ?Setting
    {
        return $this->repository->findByKey($key, $tenantId);
    }

    public function set(string $key, string $value, string $tenantId, string $type = 'string', ?string $module = null): Setting
    {
        return $this->repository->set($key, $value, $tenantId, $type, $module);
    }

    public function getByModule(string $module, string $tenantId): Collection
    {
        return $this->repository->getByModule($module, $tenantId);
    }

    public function getAllByTenant(string $tenantId): Collection
    {
        return $this->repository->allByTenant($tenantId);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
