<?php

declare(strict_types=1);

namespace Modules\OrganizationUnit\Application\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\Core\Application\Services\BaseService;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\OrganizationUnit\Application\Contracts\UploadOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment;
use Modules\OrganizationUnit\Domain\Exceptions\OrganizationUnitNotFoundException;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface;

class UploadOrganizationUnitAttachmentService extends BaseService implements UploadOrganizationUnitAttachmentServiceInterface
{
    public function __construct(
        private readonly OrganizationUnitRepositoryInterface $orgUnitRepository,
        private readonly OrganizationUnitAttachmentRepositoryInterface $attachmentRepository,
        private readonly AttachmentStorageStrategyInterface $storageStrategy
    ) {
        parent::__construct($orgUnitRepository);
    }

    /**
     * Expected $data keys:
     *   - organization_unit_id (int)
     *   - file                 (UploadedFile)
     *   - type                 (string|null)
     *   - metadata             (array|null)
     */
    protected function handle(array $data): OrganizationUnitAttachment
    {
        $orgUnitId = (int) $data['organization_unit_id'];
        /** @var UploadedFile $file */
        $file     = $data['file'];
        $type     = $data['type'] ?? null;
        $metadata = isset($data['metadata']) && is_array($data['metadata']) ? $data['metadata'] : null;

        $unit = $this->orgUnitRepository->find($orgUnitId);
        if (! $unit) {
            throw new OrganizationUnitNotFoundException($orgUnitId);
        }

        $tenantId = $unit->getTenantId();
        $uuid     = (string) Str::uuid();
        $path     = $this->storageStrategy->store($file, $orgUnitId);

        $attachment = new OrganizationUnitAttachment(
            tenantId:           $tenantId,
            organizationUnitId: $orgUnitId,
            uuid:               $uuid,
            name:               $file->getClientOriginalName(),
            filePath:           $path,
            mimeType:           (string) $file->getMimeType(),
            size:               (int) $file->getSize(),
            type:               $type,
            metadata:           $metadata,
        );

        return $this->attachmentRepository->save($attachment);
    }
}
