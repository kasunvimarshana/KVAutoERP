<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Setting;

interface SettingServiceInterface
{
    public function get(string $key, string $tenantId): ?Setting;
    public function set(string $key, string $value, string $tenantId, string $type = 'string', ?string $module = null): Setting;
    public function getByModule(string $module, string $tenantId): Collection;
    public function getAllByTenant(string $tenantId): Collection;
    public function delete(string $id): bool;
}
