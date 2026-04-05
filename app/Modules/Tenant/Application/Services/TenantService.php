<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Entities\TenantSetting;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantSettingRepositoryInterface;

final class TenantService implements TenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TenantSettingRepositoryInterface $settingRepository,
    ) {}

    public function findById(int $id): ?Tenant
    {
        return $this->tenantRepository->findById($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return $this->tenantRepository->findBySlug($slug);
    }

    public function findByDomain(string $domain): ?Tenant
    {
        return $this->tenantRepository->findByDomain($domain);
    }

    public function all(): Collection
    {
        return $this->tenantRepository->all();
    }

    public function findActive(): Collection
    {
        return $this->tenantRepository->findActive();
    }

    public function create(array $data): Tenant
    {
        return $this->tenantRepository->create($data);
    }

    public function update(int $id, array $data): ?Tenant
    {
        $tenant = $this->tenantRepository->findById($id);

        if ($tenant === null) {
            throw new NotFoundException("Tenant with ID {$id} not found.");
        }

        return $this->tenantRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $tenant = $this->tenantRepository->findById($id);

        if ($tenant === null) {
            throw new NotFoundException("Tenant with ID {$id} not found.");
        }

        return $this->tenantRepository->delete($id);
    }

    public function getSettings(int $tenantId): Collection
    {
        return $this->settingRepository->findByTenantId($tenantId);
    }

    public function getSetting(int $tenantId, string $key): ?TenantSetting
    {
        return $this->settingRepository->findByKey($tenantId, $key);
    }

    public function setSetting(int $tenantId, string $key, ?string $value, string $group = 'general'): TenantSetting
    {
        return $this->settingRepository->set($tenantId, $key, $value, $group);
    }

    public function deleteSetting(int $tenantId, string $key): bool
    {
        return $this->settingRepository->delete($tenantId, $key);
    }
}
