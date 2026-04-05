<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Setting;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\SettingModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentSettingRepository implements SettingRepositoryInterface
{
    public function __construct(private readonly SettingModel $model) {}

    public function findById(string $id): ?Setting
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByKey(string $key, string $tenantId): ?Setting
    {
        $model = $this->model
            ->where('key', $key)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (SettingModel $m) => $this->toEntity($m));
    }

    public function getByModule(string $module, string $tenantId): Collection
    {
        return $this->model
            ->where('module', $module)
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (SettingModel $m) => $this->toEntity($m));
    }

    public function set(string $key, string $value, string $tenantId, string $type = 'string', ?string $module = null): Setting
    {
        $model = $this->model->updateOrCreate(
            ['key' => $key, 'tenant_id' => $tenantId],
            ['value' => $value, 'type' => $type, 'module' => $module],
        );

        return $this->toEntity($model);
    }

    public function delete(string $id): bool
    {
        $model = $this->model->find($id);

        if (! $model) {
            throw new NotFoundException('Setting', $id);
        }

        return (bool) $model->delete();
    }

    private function toEntity(SettingModel $model): Setting
    {
        return new Setting(
            id: $model->id,
            tenantId: $model->tenant_id,
            key: $model->key,
            value: $model->value,
            type: $model->type,
            module: $model->module,
            description: $model->description,
        );
    }
}
