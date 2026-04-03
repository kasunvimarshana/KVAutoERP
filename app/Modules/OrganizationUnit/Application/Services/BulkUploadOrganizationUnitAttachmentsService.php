<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Services;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\OrganizationUnit\Application\Contracts\BulkUploadOrganizationUnitAttachmentsServiceInterface;
use Modules\OrganizationUnit\Application\DTOs\OrganizationUnitAttachmentData;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface;

class BulkUploadOrganizationUnitAttachmentsService implements BulkUploadOrganizationUnitAttachmentsServiceInterface {
    public function __construct(
        private OrganizationUnitRepositoryInterface $orgUnits,
        private OrganizationUnitAttachmentRepositoryInterface $attachments,
        private AttachmentStorageStrategyInterface $storage
    ) {}

    public function execute(array $data = []): mixed {
        $results = [];
        foreach ($data['files'] ?? [] as $file) {
            $results[] = OrganizationUnitAttachmentData::fromArray([]);
        }
        return $results;
    }
}
