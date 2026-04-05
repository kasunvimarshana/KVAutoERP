<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Language;
use Modules\Configuration\Domain\RepositoryInterfaces\LanguageRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\LanguageModel;

final class EloquentLanguageRepository implements LanguageRepositoryInterface
{
    public function __construct(
        private readonly LanguageModel $model,
    ) {}

    public function findById(int $id): ?Language
    {
        $record = $this->model->newQuery()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(?int $tenantId, string $code): ?Language
    {
        $record = $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findDefault(?int $tenantId): ?Language
    {
        $record = $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findActive(?int $tenantId): Collection
    {
        return $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->map(fn (LanguageModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Language
    {
        $record = $this->model->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Language
    {
        $record = $this->model->newQuery()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

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

    private function toEntity(LanguageModel $model): Language
    {
        return new Language(
            id: $model->id,
            tenantId: $model->tenant_id,
            code: $model->code,
            name: $model->name,
            nativeName: $model->native_name,
            isDefault: (bool) $model->is_default,
            isActive: (bool) $model->is_active,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
