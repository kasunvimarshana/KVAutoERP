<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Configuration\Domain\Entities\Setting;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\SettingModel;

class EloquentSettingRepository implements SettingRepositoryInterface
{
    public function __construct(
        private readonly SettingModel $model,
    ) {}

    public function get(int $tenantId, string $group, string $key): ?Setting
    {
        $model = $this->model->newQuery()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        return $model ? $this->mapModelToEntity($model) : null;
    }

    public function set(int $tenantId, string $group, string $key, mixed $value, string $type = 'string'): Setting
    {
        $stringValue = $value !== null ? (string) $value : null;

        $model = $this->model->newQuery()->withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => $tenantId, 'group' => $group, 'key' => $key],
            ['value' => $stringValue, 'type' => $type],
        );

        return $this->mapModelToEntity($model);
    }

    public function getGroup(int $tenantId, string $group): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('group', $group)
            ->orderBy('key')
            ->get()
            ->map(fn (SettingModel $m) => $this->mapModelToEntity($m))
            ->all();
    }

    public function getAllByTenant(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->map(fn (SettingModel $m) => $this->mapModelToEntity($m))
            ->all();
    }

    private function mapModelToEntity(SettingModel $model): Setting
    {
        return new Setting(
            id: $model->id,
            tenantId: $model->tenant_id,
            group: $model->group,
            key: $model->key,
            value: $model->value,
            type: $model->type,
            description: $model->description,
        );
    }
}
