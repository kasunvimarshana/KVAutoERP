<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\TenantSetting;

interface TenantSettingRepositoryInterface
{
    /** @return Collection<int, TenantSetting> */
    public function findByTenantId(int $tenantId): Collection;

    public function findByKey(int $tenantId, string $key): ?TenantSetting;

    public function set(int $tenantId, string $key, ?string $value, string $group = 'general'): TenantSetting;

    public function delete(int $tenantId, string $key): bool;
}
