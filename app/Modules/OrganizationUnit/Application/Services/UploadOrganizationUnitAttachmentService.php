<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Services;
use Illuminate\Support\Str;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\OrganizationUnit\Application\Contracts\UploadOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Application\DTOs\OrganizationUnitAttachmentData;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment;
use Modules\OrganizationUnit\Domain\Exceptions\OrganizationUnitNotFoundException;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface;

class UploadOrganizationUnitAttachmentService implements UploadOrganizationUnitAttachmentServiceInterface {
    public function __construct(
        private OrganizationUnitRepositoryInterface $orgUnits,
        private OrganizationUnitAttachmentRepositoryInterface $attachments,
        private AttachmentStorageStrategyInterface $storage
    ) {}

    public function execute(array $data = []): mixed {
        return $this->handle($data);
    }

    protected function handle(array $data): OrganizationUnitAttachment {
        $orgUnit = $this->orgUnits->find((int)$data['organization_unit_id']);
        if (!$orgUnit) {
            throw new OrganizationUnitNotFoundException($data['organization_unit_id']);
        }

        $file = $data['file'];
        $filePath = $this->storage->store($file, $orgUnit->getId());

        $dto = OrganizationUnitAttachmentData::fromArray([
            'organization_unit_id' => $orgUnit->getId(),
            'tenant_id' => $orgUnit->getTenantId(),
            'name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'type' => $data['type'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        $attachment = new OrganizationUnitAttachment(
            tenantId: $dto->tenant_id,
            organizationUnitId: $dto->organization_unit_id,
            uuid: (string)Str::uuid(),
            name: $dto->name,
            filePath: $dto->file_path,
            mimeType: $dto->mime_type,
            size: $dto->size,
            type: $dto->type,
            metadata: $dto->metadata,
        );

        return $this->attachments->save($attachment);
    }
}
