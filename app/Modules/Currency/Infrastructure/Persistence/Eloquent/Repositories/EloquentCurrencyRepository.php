<?php

declare(strict_types=1);

namespace Modules\Currency\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Currency\Domain\Entities\Currency;
use Modules\Currency\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Models\CurrencyModel;

class EloquentCurrencyRepository implements CurrencyRepositoryInterface
{
    public function __construct(
        private readonly CurrencyModel $model,
    ) {}

    public function findById(int $id): ?Currency
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(int $tenantId, string $code): ?Currency
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findDefault(int $tenantId): ?Currency
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function all(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (CurrencyModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Currency
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Currency
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

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
            decimalPlaces: $model->decimal_places,
            isDefault: (bool) $model->is_default,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at,
        );
    }
}
