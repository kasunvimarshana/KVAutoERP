<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Barcode\Domain\Entities\LabelTemplate;
use Modules\Barcode\Domain\RepositoryInterfaces\LabelTemplateRepositoryInterface;
use Modules\Barcode\Infrastructure\Persistence\Eloquent\Models\LabelTemplateModel;

class EloquentLabelTemplateRepository implements LabelTemplateRepositoryInterface
{
    public function __construct(
        private readonly LabelTemplateModel $model,
    ) {}

    public function create(array $data): LabelTemplate
    {
        $record = $this->model->newInstance();
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): LabelTemplate
    {
        $record = $this->model->withoutGlobalScopes()->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function delete(int $id): void
    {
        $this->model->withoutGlobalScopes()->findOrFail($id)->delete();
    }

    public function findById(int $id, int $tenantId): ?LabelTemplate
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

    private function toEntity(LabelTemplateModel $model): LabelTemplate
    {
        return new LabelTemplate(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            name: (string) $model->name,
            format: (string) $model->format,
            template: (string) $model->template,
            width: $model->width !== null ? (float) $model->width : null,
            height: $model->height !== null ? (float) $model->height : null,
            variables: (array) ($model->variables ?? []),
            createdAt: $model->created_at,
        );
    }
}
