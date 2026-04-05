<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Modules\Product\Domain\Entities\Attachment;

interface AttachmentRepositoryInterface
{
    public function findById(int $id): ?Attachment;

    /** @return Attachment[] */
    public function findByEntity(string $attachableType, int $attachableId): array;

    public function create(array $data): Attachment;

    public function delete(int $id): bool;
}
