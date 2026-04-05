<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Barcode\Domain\Entities\BarcodePrintJob;
use Modules\Barcode\Domain\RepositoryInterfaces\BarcodePrintJobRepositoryInterface;
use Modules\Barcode\Infrastructure\Persistence\Eloquent\Models\BarcodePrintJobModel;

class EloquentBarcodePrintJobRepository implements BarcodePrintJobRepositoryInterface
{
    public function __construct(
        private readonly BarcodePrintJobModel $model,
    ) {}

    public function create(array $data): BarcodePrintJob
    {
        $record = $this->model->newInstance();
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): BarcodePrintJob
    {
        $record = $this->model->withoutGlobalScopes()->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function findById(int $id, int $tenantId): ?BarcodePrintJob
    {
        $record = $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->find($id);

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

    private function toEntity(BarcodePrintJobModel $model): BarcodePrintJob
    {
        return new BarcodePrintJob(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            labelTemplateId: $model->label_template_id !== null ? (int) $model->label_template_id : null,
            barcodeId: $model->barcode_id !== null ? (int) $model->barcode_id : null,
            status: (string) $model->status,
            printerId: $model->printer_id,
            copies: (int) $model->copies,
            printedAt: $model->printed_at,
            errorMessage: $model->error_message,
            createdAt: $model->created_at,
        );
    }
}
