<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Configuration\Domain\Entities\SystemConfig;
use Modules\Configuration\Domain\Repositories\SystemConfigRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Models\SystemConfigModel;

class EloquentSystemConfigRepository implements SystemConfigRepositoryInterface
{
    public function __construct(
        private readonly SystemConfigModel $model,
    ) {}

    public function findById(int $id): ?SystemConfig
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByKey(string $key, ?int $tenantId = null): ?SystemConfig
    {
        $model = $this->model
            ->where('key', $key)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(?int $tenantId = null, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $query = $tenantId !== null
            ? $this->model->where('tenant_id', $tenantId)
            : $this->model->newQuery();

        return $query
            ->orderBy('group')
            ->orderBy('key')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (SystemConfigModel $m) => $this->toEntity($m));
    }

    public function upsert(string $key, ?string $value, ?int $tenantId, string $group = 'general'): SystemConfig
    {
        $model = $this->model->updateOrCreate(
            ['key' => $key, 'tenant_id' => $tenantId],
            ['value' => $value, 'group' => $group],
        );

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = $this->model->find($id);

        return $model ? (bool) $model->delete() : false;
    }

    private function toEntity(SystemConfigModel $model): SystemConfig
    {
        return new SystemConfig(
            id: $model->id,
            tenantId: $model->tenant_id,
            key: $model->key,
            value: $model->value,
            group: $model->group,
            description: $model->description,
            isSystem: (bool) $model->is_system,
        );
    }
}
