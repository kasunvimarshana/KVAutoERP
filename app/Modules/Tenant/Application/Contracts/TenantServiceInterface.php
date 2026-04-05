<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Entities\TenantSetting;

interface TenantServiceInterface
{
    public function findById(int $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    public function findByDomain(string $domain): ?Tenant;

    /** @return Collection<int, Tenant> */
    public function all(): Collection;

    /** @return Collection<int, Tenant> */
    public function findActive(): Collection;

    public function create(array $data): Tenant;

    public function update(int $id, array $data): ?Tenant;

    public function delete(int $id): bool;

    /** @return Collection<int, TenantSetting> */
    public function getSettings(int $tenantId): Collection;

    public function getSetting(int $tenantId, string $key): ?TenantSetting;

    public function setSetting(int $tenantId, string $key, ?string $value, string $group = 'general'): TenantSetting;

    public function deleteSetting(int $tenantId, string $key): bool;
}
