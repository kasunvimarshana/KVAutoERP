<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Setting;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\SettingModel;

final class EloquentSettingRepository implements SettingRepositoryInterface
{
    public function __construct(
        private readonly SettingModel $model,
    ) {}

    public function findByKey(?int $tenantId, string $key): ?Setting
    {
        $record = $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenant(?int $tenantId): Collection
    {
        return $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (SettingModel $m) => $this->toEntity($m));
    }

    public function findByGroup(?int $tenantId, string $group): Collection
    {
        return $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('group', $group)
            ->get()
            ->map(fn (SettingModel $m) => $this->toEntity($m));
    }

    public function set(?int $tenantId, string $key, ?string $value, string $type, string $group): Setting
    {
        $record = $this->model->newQuery()
            ->updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => $key],
                ['value' => $value, 'type' => $type, 'group' => $group],
            );

        return $this->toEntity($record);
    }

    public function delete(?int $tenantId, string $key): bool
    {
        return (bool) $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->delete();
    }

    public function bulkSet(?int $tenantId, array $items): Collection
    {
        $entities = new Collection();

        foreach ($items as $key => $item) {
            $entities->push(
                $this->set($tenantId, $key, $item['value'], $item['type'], $item['group'])
            );
        }

        return $entities;
    }

    private function toEntity(SettingModel $model): Setting
    {
        return new Setting(
            id: $model->id,
            tenantId: $model->tenant_id,
            key: $model->key,
            value: $model->value,
            type: $model->type,
            group: $model->group,
            isPublic: (bool) $model->is_public,
            description: $model->description,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
