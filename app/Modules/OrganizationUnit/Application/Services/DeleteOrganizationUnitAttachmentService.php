<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Services;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\OrganizationUnit\Application\Contracts\DeleteOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Domain\Exceptions\AttachmentNotFoundException;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;

class DeleteOrganizationUnitAttachmentService implements DeleteOrganizationUnitAttachmentServiceInterface {
    public function __construct(
        private OrganizationUnitAttachmentRepositoryInterface $attachments,
        private AttachmentStorageStrategyInterface $storage
    ) {}

    public function execute(array $data = []): mixed {
        return $this->handle($data);
    }

    protected function handle(array $data): bool {
        $attachment = $this->attachments->find((int)$data['attachment_id']);
        if (!$attachment) {
            throw new AttachmentNotFoundException($data['attachment_id']);
        }

        $this->storage->delete($attachment->getFilePath());
        return $this->attachments->delete($attachment->getId());
    }
}
