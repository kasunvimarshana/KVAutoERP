<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\RepositoryInterfaces;

use Modules\Configuration\Domain\Entities\Setting;

interface SettingRepositoryInterface
{
    public function findById(int $id): ?Setting;

    public function findByKey(int $tenantId, string $key): ?Setting;

    /** @return Setting[] */
    public function findByGroup(int $tenantId, string $group): array;

    /** @return Setting[] */
    public function allForTenant(int $tenantId): array;

    public function set(int $tenantId, string $key, mixed $value, string $group = 'general', string $type = 'string'): Setting;

    public function delete(int $id): bool;
}
