<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;

class OrganizationUnitAttachmentResource extends JsonResource {
    public function toArray($request): array {
        /** @var \Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment $attachment */
        $attachment = $this->resource;
        $strategy = app(AttachmentStorageStrategyInterface::class);
        return [
            'id' => $attachment->getId(),
            'uuid' => $attachment->getUuid(),
            'name' => $attachment->getName(),
            'file_path' => $attachment->getFilePath(),
            'url' => $strategy->url($attachment->getFilePath()),
            'mime_type' => $attachment->getMimeType(),
            'size' => $attachment->getSize(),
            'type' => $attachment->getType(),
        ];
    }
}
