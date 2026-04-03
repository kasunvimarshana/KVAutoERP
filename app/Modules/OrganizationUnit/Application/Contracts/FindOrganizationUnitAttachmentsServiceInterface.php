<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Contracts;
use Illuminate\Support\Collection;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment;

interface FindOrganizationUnitAttachmentsServiceInterface {
    public function findByOrganizationUnit(int $orgUnitId, ?string $type = null): Collection;
    public function findByUuid(string $uuid): ?OrganizationUnitAttachment;
    public function find(int $id): ?OrganizationUnitAttachment;
}
