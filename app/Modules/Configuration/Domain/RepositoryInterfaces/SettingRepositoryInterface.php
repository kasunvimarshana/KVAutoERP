<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Setting;

interface SettingRepositoryInterface
{
    public function findById(string $id): ?Setting;
    public function findByKey(string $key, string $tenantId): ?Setting;
    public function allByTenant(string $tenantId): Collection;
    public function getByModule(string $module, string $tenantId): Collection;
    public function set(string $key, string $value, string $tenantId, string $type = 'string', ?string $module = null): Setting;
    public function delete(string $id): bool;
}
