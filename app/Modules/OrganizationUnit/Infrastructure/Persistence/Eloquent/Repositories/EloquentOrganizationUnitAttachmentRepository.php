<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;

class EloquentOrganizationUnitAttachmentRepository implements OrganizationUnitAttachmentRepositoryInterface {
    public function find(int $id): ?OrganizationUnitAttachment { return null; }
    public function findByUuid(string $uuid): ?OrganizationUnitAttachment { return null; }
    public function getByOrganizationUnit(int $orgUnitId, ?string $type = null): Collection { return collect(); }
    public function save(OrganizationUnitAttachment $attachment): OrganizationUnitAttachment { return $attachment; }
    public function delete(int $id): bool { return false; }
}
