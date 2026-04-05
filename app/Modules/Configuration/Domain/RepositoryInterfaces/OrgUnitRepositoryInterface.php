<?php declare(strict_types=1);
namespace Modules\Configuration\Domain\RepositoryInterfaces;
use Modules\Configuration\Domain\Entities\OrgUnit;
interface OrgUnitRepositoryInterface {
    public function findById(int $id): ?OrgUnit;
    public function findByTenant(int $tenantId): array;
    public function findDescendants(int $id): array;
    public function findAncestors(int $id): array;
    public function save(OrgUnit $orgUnit): OrgUnit;
    public function delete(int $id): void;
}
