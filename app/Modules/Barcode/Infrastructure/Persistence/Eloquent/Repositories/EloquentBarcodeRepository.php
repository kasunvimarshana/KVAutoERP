<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Barcode\Domain\Entities\Barcode;
use Modules\Barcode\Domain\RepositoryInterfaces\BarcodeRepositoryInterface;
use Modules\Barcode\Infrastructure\Persistence\Eloquent\Models\BarcodeModel;

class EloquentBarcodeRepository implements BarcodeRepositoryInterface
{
    public function __construct(
        private readonly BarcodeModel $model,
    ) {}

    public function create(array $data): Barcode
    {
        $record = $this->model->newInstance();
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function findById(int $id, int $tenantId): ?Barcode
    {
        $record = $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByData(string $data, int $tenantId): ?Barcode
    {
        $record = $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('data', $data)
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

    private function toEntity(BarcodeModel $model): Barcode
    {
        return new Barcode(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            symbology: (string) $model->symbology,
            data: (string) $model->data,
            checkDigit: $model->check_digit,
            encodedData: (string) $model->encoded_data,
            generatedAt: $model->generated_at,
            metadata: (array) ($model->metadata ?? []),
        );
    }
}
