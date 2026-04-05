<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Product\Domain\Entities\Attachment;
use Modules\Product\Domain\RepositoryInterfaces\AttachmentRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\AttachmentModel;

class EloquentAttachmentRepository implements AttachmentRepositoryInterface
{
    public function __construct(
        private readonly AttachmentModel $model,
    ) {}

    public function findById(int $id): ?Attachment
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByEntity(string $attachableType, int $attachableId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('attachable_type', $attachableType)
            ->where('attachable_id', $attachableId)
            ->get()
            ->map(fn (AttachmentModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Attachment
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    private function toEntity(AttachmentModel $model): Attachment
    {
        return new Attachment(
            id: $model->id,
            tenantId: $model->tenant_id,
            attachableType: $model->attachable_type,
            attachableId: $model->attachable_id,
            filename: $model->filename,
            originalName: $model->original_name,
            mimeType: $model->mime_type,
            size: (int) $model->size,
            disk: $model->disk,
            path: $model->path,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
