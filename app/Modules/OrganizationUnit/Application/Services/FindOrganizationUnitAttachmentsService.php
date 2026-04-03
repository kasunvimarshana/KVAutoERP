<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Services;
use Illuminate\Support\Collection;
use Modules\OrganizationUnit\Application\Contracts\FindOrganizationUnitAttachmentsServiceInterface;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;

class FindOrganizationUnitAttachmentsService implements FindOrganizationUnitAttachmentsServiceInterface {
    public function __construct(private OrganizationUnitAttachmentRepositoryInterface $attachments) {}

    public function findByOrganizationUnit(int $orgUnitId, ?string $type = null): Collection {
        return $this->attachments->getByOrganizationUnit($orgUnitId, $type);
    }

    public function findByUuid(string $uuid): ?OrganizationUnitAttachment {
        return $this->attachments->findByUuid($uuid);
    }

    public function find(int $id): ?OrganizationUnitAttachment {
        return $this->attachments->find($id);
    }
}
