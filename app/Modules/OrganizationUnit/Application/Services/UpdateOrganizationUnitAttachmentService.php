<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Services;
use Modules\OrganizationUnit\Application\Contracts\UpdateOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Application\DTOs\UpdateOrganizationUnitAttachmentData;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment;
use Modules\OrganizationUnit\Domain\Exceptions\AttachmentNotFoundException;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;

class UpdateOrganizationUnitAttachmentService implements UpdateOrganizationUnitAttachmentServiceInterface {
    public function __construct(private OrganizationUnitAttachmentRepositoryInterface $attachments) {}

    public function execute(array $data = []): mixed {
        return $this->handle($data);
    }

    protected function handle(array $data): OrganizationUnitAttachment {
        $attachment = $this->attachments->find((int)$data['attachment_id']);
        if (!$attachment) {
            throw new AttachmentNotFoundException($data['attachment_id']);
        }

        $dto = UpdateOrganizationUnitAttachmentData::fromArray($data);

        $type = array_key_exists('type', $data) ? $data['type'] : $attachment->getType();
        $metadata = array_key_exists('metadata', $data) ? $data['metadata'] : $attachment->getMetadata();

        $attachment->updateDetails($type, $metadata);

        return $this->attachments->save($attachment);
    }
}
