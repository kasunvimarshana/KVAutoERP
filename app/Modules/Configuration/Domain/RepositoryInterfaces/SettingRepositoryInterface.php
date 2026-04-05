<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Setting;

interface SettingRepositoryInterface
{
    public function findByKey(?int $tenantId, string $key): ?Setting;

    /** @return Collection<int, Setting> */
    public function findByTenant(?int $tenantId): Collection;

    /** @return Collection<int, Setting> */
    public function findByGroup(?int $tenantId, string $group): Collection;

    public function set(?int $tenantId, string $key, ?string $value, string $type, string $group): Setting;

    public function delete(?int $tenantId, string $key): bool;

    /**
     * @param  array<string, array{value: ?string, type: string, group: string}>  $items
     * @return Collection<int, Setting>
     */
    public function bulkSet(?int $tenantId, array $items): Collection;
}
