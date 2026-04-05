<?php

declare(strict_types=1);

namespace Modules\Currency\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Currency\Domain\Entities\ExchangeRate;
use Modules\Currency\Domain\RepositoryInterfaces\ExchangeRateRepositoryInterface;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Models\ExchangeRateModel;

class EloquentExchangeRateRepository implements ExchangeRateRepositoryInterface
{
    public function __construct(
        private readonly ExchangeRateModel $model,
    ) {}

    public function findById(int $id): ?ExchangeRate
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findRate(int $tenantId, string $fromCurrency, string $toCurrency, ?\DateTimeInterface $date = null): ?ExchangeRate
    {
        $query = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency);

        if ($date !== null) {
            $query->where('effective_date', '<=', $date->format('Y-m-d'));
        }

        $record = $query->orderByDesc('effective_date')->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function create(array $data): ExchangeRate
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?ExchangeRate
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

    private function toEntity(ExchangeRateModel $model): ExchangeRate
    {
        return new ExchangeRate(
            id: $model->id,
            tenantId: $model->tenant_id,
            fromCurrency: $model->from_currency,
            toCurrency: $model->to_currency,
            rate: (float) $model->rate,
            effectiveDate: $model->effective_date,
            source: $model->source,
            createdAt: $model->created_at,
        );
    }
}
