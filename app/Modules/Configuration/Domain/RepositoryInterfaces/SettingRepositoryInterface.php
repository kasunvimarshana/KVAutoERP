<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\RepositoryInterfaces;

use Modules\Configuration\Domain\Entities\Setting;

interface SettingRepositoryInterface
{
    public function get(int $tenantId, string $group, string $key): ?Setting;

    public function set(int $tenantId, string $group, string $key, mixed $value, string $type = 'string'): Setting;

    public function getGroup(int $tenantId, string $group): array;

    public function getAllByTenant(int $tenantId): array;
}
