<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Currency;
use Modules\Configuration\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\CurrencyModel;

final class EloquentCurrencyRepository implements CurrencyRepositoryInterface
{
    public function __construct(
        private readonly CurrencyModel $model,
    ) {}

    public function findById(int $id): ?Currency
    {
        $record = $this->model->newQuery()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(?int $tenantId, string $code): ?Currency
    {
        $record = $this->model->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findDefault(?int $tenantId): ?Currency
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
            ->map(fn (CurrencyModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Currency
    {
        $record = $this->model->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Currency
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

    private function toEntity(CurrencyModel $model): Currency
    {
        return new Currency(
            id: $model->id,
            tenantId: $model->tenant_id,
            code: $model->code,
            name: $model->name,
            symbol: $model->symbol,
            decimalPlaces: (int) $model->decimal_places,
            isDefault: (bool) $model->is_default,
            isActive: (bool) $model->is_active,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
