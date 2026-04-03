<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnit;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface;

class EloquentOrganizationUnitRepository implements OrganizationUnitRepositoryInterface {
    public function find(int $id): ?OrganizationUnit { return null; }
    public function save(OrganizationUnit $unit): OrganizationUnit { return $unit; }
    public function delete(int $id): bool { return false; }
    public function moveNode(OrganizationUnit $unit): OrganizationUnit { return $unit; }
    public function getTree(int $tenantId, ?int $rootId = null): array { return []; }
    public function getDescendants(int $id): array { return []; }
    public function getAncestors(int $id): array { return []; }
    public function all(): Collection { return collect(); }
}
