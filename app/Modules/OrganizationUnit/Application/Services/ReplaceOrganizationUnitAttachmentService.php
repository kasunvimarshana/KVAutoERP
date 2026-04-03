<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Services;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\OrganizationUnit\Application\Contracts\ReplaceOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Application\DTOs\OrganizationUnitAttachmentData;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment;
use Modules\OrganizationUnit\Domain\Exceptions\AttachmentNotFoundException;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;

class ReplaceOrganizationUnitAttachmentService implements ReplaceOrganizationUnitAttachmentServiceInterface {
    public function __construct(
        private OrganizationUnitAttachmentRepositoryInterface $attachments,
        private AttachmentStorageStrategyInterface $storage
    ) {}

    public function execute(array $data = []): mixed {
        return $this->handle($data);
    }

    protected function handle(array $data): OrganizationUnitAttachment {
        $attachment = $this->attachments->find((int)$data['attachment_id']);
        if (!$attachment) {
            throw new AttachmentNotFoundException($data['attachment_id']);
        }

        $file = $data['file'];
        $this->storage->delete($attachment->getFilePath());
        $newPath = $this->storage->store($file, $attachment->getOrganizationUnitId());

        $dto = OrganizationUnitAttachmentData::fromArray([
            'organization_unit_id' => $attachment->getOrganizationUnitId(),
            'tenant_id' => $attachment->getTenantId(),
            'name' => $file->getClientOriginalName(),
            'file_path' => $newPath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $attachment->updateFile(
            $dto->name,
            $dto->file_path,
            $dto->mime_type,
            $dto->size
        );

        return $this->attachments->save($attachment);
    }
}
