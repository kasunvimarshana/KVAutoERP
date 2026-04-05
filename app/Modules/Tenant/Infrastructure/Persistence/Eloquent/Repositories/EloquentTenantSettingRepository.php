<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\TenantSetting;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantSettingRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantSettingModel;

final class EloquentTenantSettingRepository implements TenantSettingRepositoryInterface
{
    public function __construct(
        private readonly TenantSettingModel $model,
    ) {}

    public function findByTenantId(int $tenantId): Collection
    {
        return $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (TenantSettingModel $m) => $this->toEntity($m));
    }

    public function findByKey(int $tenantId, string $key): ?TenantSetting
    {
        $record = $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function set(int $tenantId, string $key, ?string $value, string $group = 'general'): TenantSetting
    {
        $record = $this->model->newQuery()
            ->updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => $key],
                ['value' => $value, 'group' => $group],
            );

        return $this->toEntity($record);
    }

    public function delete(int $tenantId, string $key): bool
    {
        return (bool) $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->delete();
    }

    private function toEntity(TenantSettingModel $model): TenantSetting
    {
        return new TenantSetting(
            id: $model->id,
            tenantId: $model->tenant_id,
            key: $model->key,
            value: $model->value,
            group: $model->group,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
