<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Collection;
use Modules\Configuration\Application\Contracts\SettingServiceInterface;
use Modules\Configuration\Domain\Entities\Setting;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;

final class SettingService implements SettingServiceInterface
{
    public function __construct(
        private readonly SettingRepositoryInterface $repository,
    ) {}

    public function get(?int $tenantId, string $key, mixed $default = null): mixed
    {
        $setting = $this->repository->findByKey($tenantId, $key);

        return $setting !== null ? $setting->getValue() : $default;
    }

    public function set(
        ?int $tenantId,
        string $key,
        ?string $value,
        string $type = Setting::TYPE_STRING,
        string $group = 'general',
    ): Setting {
        return $this->repository->set($tenantId, $key, $value, $type, $group);
    }

    public function bulkGet(?int $tenantId, array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $setting = $this->repository->findByKey($tenantId, $key);
            $result[$key] = $setting !== null ? $setting->getValue() : null;
        }

        return $result;
    }

    public function bulkSet(?int $tenantId, array $items): Collection
    {
        return $this->repository->bulkSet($tenantId, $items);
    }

    public function getGroup(?int $tenantId, string $group): Collection
    {
        return $this->repository->findByGroup($tenantId, $group);
    }

    public function delete(?int $tenantId, string $key): bool
    {
        return $this->repository->delete($tenantId, $key);
    }
}
