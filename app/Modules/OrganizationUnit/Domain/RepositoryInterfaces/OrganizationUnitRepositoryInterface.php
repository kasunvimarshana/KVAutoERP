<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Domain\RepositoryInterfaces;
use Illuminate\Support\Collection;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnit;

interface OrganizationUnitRepositoryInterface {
    public function find(int $id): ?OrganizationUnit;
    public function save(OrganizationUnit $unit): OrganizationUnit;
    public function delete(int $id): bool;
    public function moveNode(OrganizationUnit $unit): OrganizationUnit;
    public function getTree(int $tenantId, ?int $rootId = null): array;
    public function getDescendants(int $id): array;
    public function getAncestors(int $id): array;
    public function all(): Collection;
}
