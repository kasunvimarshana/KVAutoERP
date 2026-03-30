<?php

declare(strict_types=1);

namespace Modules\OrganizationUnit\Application\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\OrganizationUnit\Application\Contracts\BulkUploadOrganizationUnitAttachmentsServiceInterface;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment;
use Modules\OrganizationUnit\Domain\Exceptions\OrganizationUnitNotFoundException;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface;

class BulkUploadOrganizationUnitAttachmentsService implements BulkUploadOrganizationUnitAttachmentsServiceInterface
{
    public function __construct(
        private readonly OrganizationUnitRepositoryInterface $orgUnitRepository,
        private readonly OrganizationUnitAttachmentRepositoryInterface $attachmentRepository,
        private readonly AttachmentStorageStrategyInterface $storageStrategy
    ) {}

    /**
     * Upload multiple attachments inside a single transaction.
     *
     * Expected $data keys:
     *   - organization_unit_id (int)
     *   - files                (UploadedFile[])
     *   - type                 (string|null)
     *   - metadata             (array|null)
     *
     * @return Collection<int, OrganizationUnitAttachment>
     */
    public function execute(array $data): Collection
    {
        return DB::transaction(function () use ($data): Collection {
            $orgUnitId = (int) $data['organization_unit_id'];
            $files     = $data['files'] ?? [];
            $type      = $data['type'] ?? null;
            $metadata  = isset($data['metadata']) && is_array($data['metadata']) ? $data['metadata'] : null;

            $unit = $this->orgUnitRepository->find($orgUnitId);
            if (! $unit) {
                throw new OrganizationUnitNotFoundException($orgUnitId);
            }

            $tenantId = $unit->getTenantId();
            $saved    = new Collection;

            foreach ($files as $file) {
                /** @var UploadedFile $file */
                $uuid = (string) Str::uuid();
                $path = $this->storageStrategy->store($file, $orgUnitId);

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

                $saved->push($this->attachmentRepository->save($attachment));
            }

            return $saved;
        });
    }
}
