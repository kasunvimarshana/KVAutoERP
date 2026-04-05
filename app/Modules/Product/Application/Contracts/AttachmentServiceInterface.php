<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\Attachment;

interface AttachmentServiceInterface
{
    /**
     * @param array{filename: string, originalName: string, mimeType: string, size: int, disk: string, path: string} $file
     */
    public function attach(
        int $tenantId,
        string $attachableType,
        int $attachableId,
        array $file,
    ): Attachment;

    public function detach(int $id): bool;

    /** @return Attachment[] */
    public function getAttachments(string $attachableType, int $attachableId): array;
}
