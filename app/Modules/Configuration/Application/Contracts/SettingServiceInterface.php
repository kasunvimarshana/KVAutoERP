<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Modules\Configuration\Domain\Entities\Setting;

interface SettingServiceInterface
{
    public function get(int $tenantId, string $key): ?Setting;

    public function set(int $tenantId, string $key, mixed $value, string $group = 'general', string $type = 'string'): Setting;

    /** @return Setting[] */
    public function getGroup(int $tenantId, string $group): array;

    /** @return Setting[] */
    public function getAll(int $tenantId): array;
}
