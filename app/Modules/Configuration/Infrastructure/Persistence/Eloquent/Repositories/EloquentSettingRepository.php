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

    public function findById(int $id): ?Setting
    {
        $record = $this->model->newQuery()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByKey(int $tenantId, string $key): ?Setting
    {
        $record = $this->model
            ->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByGroup(int $tenantId, string $group): array
    {
        return $this->model
            ->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('group', $group)
            ->get()
            ->map(fn (SettingModel $m) => $this->toEntity($m))
            ->all();
    }

    public function allForTenant(int $tenantId): array
    {
        return $this->model
            ->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (SettingModel $m) => $this->toEntity($m))
            ->all();
    }

    public function set(int $tenantId, string $key, mixed $value, string $group = 'general', string $type = 'string'): Setting
    {
        $record = $this->model
            ->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();

        $payload = [
            'tenant_id' => $tenantId,
            'key'       => $key,
            'value'     => (string) (is_array($value) || is_object($value) ? json_encode($value) : $value),
            'group'     => $group,
            'type'      => $type,
        ];

        if ($record === null) {
            $record = $this->model->newQuery()->create($payload);
        } else {
            $record->fill($payload)->save();
        }

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQuery()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    private function toEntity(SettingModel $model): Setting
    {
        return new Setting(
            id: $model->id,
            tenantId: $model->tenant_id,
            key: $model->key,
            value: $model->value,
            group: $model->group,
            type: $model->type,
            isSystem: (bool) $model->is_system,
            description: $model->description,
            updatedAt: $model->updated_at,
        );
    }
}
