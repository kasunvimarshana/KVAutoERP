<?php

declare(strict_types=1);

namespace Modules\Currency\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Currency\Domain\Entities\Currency;
use Modules\Currency\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Models\CurrencyModel;

class EloquentCurrencyRepository implements CurrencyRepositoryInterface
{
    public function __construct(
        private readonly CurrencyModel $model,
    ) {}

    public function create(array $data): Currency
    {
        $record = $this->model->newInstance();
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): Currency
    {
        $record = $this->model->withoutGlobalScopes()->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function findById(int $id, int $tenantId): ?Currency
    {
        $record = $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(string $code, int $tenantId): ?Currency
    {
        $record = $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function listAll(int $tenantId): array
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn ($r) => $this->toEntity($r))
            ->all();
    }

    public function clearDefault(int $tenantId): void
    {
        $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->update(['is_default' => false]);
    }

    private function toEntity(CurrencyModel $model): Currency
    {
        return new Currency(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            code: (string) $model->code,
            name: (string) $model->name,
            symbol: (string) $model->symbol,
            decimalPlaces: (int) $model->decimal_places,
            isDefault: (bool) $model->is_default,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at,
        );
    }
}
