<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Product\Application\Contracts\AttachmentServiceInterface;
use Modules\Product\Domain\Entities\Attachment;
use Modules\Product\Domain\RepositoryInterfaces\AttachmentRepositoryInterface;

class AttachmentService implements AttachmentServiceInterface
{
    public function __construct(
        private readonly AttachmentRepositoryInterface $repository,
    ) {}

    public function attach(
        int $tenantId,
        string $attachableType,
        int $attachableId,
        array $file,
    ): Attachment {
        return $this->repository->create([
            'tenant_id'       => $tenantId,
            'attachable_type' => $attachableType,
            'attachable_id'   => $attachableId,
            'filename'        => $file['filename'],
            'original_name'   => $file['originalName'],
            'mime_type'       => $file['mimeType'],
            'size'            => $file['size'],
            'disk'            => $file['disk'],
            'path'            => $file['path'],
        ]);
    }

    public function detach(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getAttachments(string $attachableType, int $attachableId): array
    {
        return $this->repository->findByEntity($attachableType, $attachableId);
    }
}
