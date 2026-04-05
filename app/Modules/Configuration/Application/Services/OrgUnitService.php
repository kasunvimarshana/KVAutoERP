<?php declare(strict_types=1);
namespace Modules\Configuration\Application\Services;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;
class OrgUnitService {
    public function __construct(private readonly OrgUnitRepositoryInterface $repo) {}
    public function create(array $data): OrgUnit {
        $parentPath = '/';
        $level = 0;
        if (isset($data['parent_id'])) {
            $parent = $this->repo->findById($data['parent_id']);
            if (!$parent) throw new NotFoundException("Parent OrgUnit {$data['parent_id']} not found");
            $parentPath = $parent->getPath();
            $level = $parent->getLevel() + 1;
        }
        $tempUnit = new OrgUnit(null,$data['tenant_id'],$data['name'],$data['code'],$data['type']??'department',$data['parent_id']??null,$parentPath,0,true);
        $saved = $this->repo->save($tempUnit);
        $finalPath = $parentPath . $saved->getId() . '/';
        $final = new OrgUnit($saved->getId(),$saved->getTenantId(),$saved->getName(),$saved->getCode(),$saved->getType(),$saved->getParentId(),$finalPath,$level,$saved->isActive());
        return $this->repo->save($final);
    }
    public function findById(int $id): ?OrgUnit { return $this->repo->findById($id); }
    public function getTree(int $tenantId): array { return $this->repo->findByTenant($tenantId); }
    public function getDescendants(int $id): array { return $this->repo->findDescendants($id); }
    public function getAncestors(int $id): array { return $this->repo->findAncestors($id); }
    public function delete(int $id): void { $this->repo->delete($id); }
}
