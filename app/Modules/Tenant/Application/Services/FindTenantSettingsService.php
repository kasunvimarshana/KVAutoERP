<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Collection;
use Modules\Tenant\Application\Contracts\FindTenantSettingsServiceInterface;
use Modules\Tenant\Domain\Entities\TenantSetting;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantSettingRepositoryInterface;

class FindTenantSettingsService implements FindTenantSettingsServiceInterface
{
    public function __construct(
        private readonly TenantSettingRepositoryInterface $settingRepository
    ) {}

    public function find(int $id): ?TenantSetting
    {
        return $this->settingRepository->find($id);
    }

    public function findByTenantAndKey(int $tenantId, string $key): ?TenantSetting
    {
        return $this->settingRepository->findByTenantAndKey($tenantId, $key);
    }

    public function listByTenant(int $tenantId, ?string $group = null, ?bool $isPublic = null): Collection
    {
        return $this->settingRepository->getByTenant($tenantId, $group, $isPublic);
    }
}
