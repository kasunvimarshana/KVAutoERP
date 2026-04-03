<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Contracts;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnit;

interface FindOrganizationUnitServiceInterface {
    public function find(int $id): ?OrganizationUnit;
    public function list(array $filters = []): mixed;
    public function getTree(int $tenantId, ?int $rootId = null): array;
    public function getDescendants(int $id): array;
    public function getAncestors(int $id): array;
}
