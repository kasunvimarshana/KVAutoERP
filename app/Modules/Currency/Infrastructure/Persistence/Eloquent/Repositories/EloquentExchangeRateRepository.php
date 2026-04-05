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

    public function create(array $data): ExchangeRate
    {
        $record = $this->model->newInstance();
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function findLatest(string $from, string $to, int $tenantId, ?\DateTimeInterface $date = null): ?ExchangeRate
    {
        $query = $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('from_currency', $from)
            ->where('to_currency', $to);

        if ($date !== null) {
            $query->where('effective_date', '<=', $date->format('Y-m-d'));
        }

        $record = $query->orderByDesc('effective_date')->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function listForPair(string $from, string $to, int $tenantId): array
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('from_currency', $from)
            ->where('to_currency', $to)
            ->orderByDesc('effective_date')
            ->get()
            ->map(fn ($r) => $this->toEntity($r))
            ->all();
    }

    private function toEntity(ExchangeRateModel $model): ExchangeRate
    {
        return new ExchangeRate(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            fromCurrency: (string) $model->from_currency,
            toCurrency: (string) $model->to_currency,
            rate: (float) $model->rate,
            effectiveDate: $model->effective_date,
            source: (string) $model->source,
            createdAt: $model->created_at,
        );
    }
}
