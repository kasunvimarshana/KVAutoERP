<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Services;
use Modules\OrganizationUnit\Application\Contracts\FindOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnit;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface;

class FindOrganizationUnitService implements FindOrganizationUnitServiceInterface {
    public function __construct(private OrganizationUnitRepositoryInterface $repo) {}

    public function find(int $id): ?OrganizationUnit {
        return $this->repo->find($id);
    }

    public function list(array $filters = []): mixed {
        return $this->repo->all();
    }

    public function getTree(int $tenantId, ?int $rootId = null): array {
        return $this->repo->getTree($tenantId, $rootId);
    }

    public function getDescendants(int $id): array {
        return $this->repo->getDescendants($id);
    }

    public function getAncestors(int $id): array {
        return $this->repo->getAncestors($id);
    }

    public function execute(array $data = []): mixed {
        return $this->handle($data);
    }

    protected function handle(array $data): mixed {
        throw new \BadMethodCallException('Use find(), list(), or getTree() instead.');
    }
}
