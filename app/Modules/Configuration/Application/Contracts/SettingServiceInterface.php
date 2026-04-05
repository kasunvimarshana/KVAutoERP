<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Setting;

interface SettingServiceInterface
{
    public function get(?int $tenantId, string $key, mixed $default = null): mixed;

    public function set(?int $tenantId, string $key, ?string $value, string $type = Setting::TYPE_STRING, string $group = 'general'): Setting;

    /** @return array<string, mixed> */
    public function bulkGet(?int $tenantId, array $keys): array;

    /**
     * @param  array<string, array{value: ?string, type: string, group: string}>  $items
     * @return Collection<int, Setting>
     */
    public function bulkSet(?int $tenantId, array $items): Collection;

    /** @return Collection<int, Setting> */
    public function getGroup(?int $tenantId, string $group): Collection;

    public function delete(?int $tenantId, string $key): bool;
}
