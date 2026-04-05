<?php declare(strict_types=1);
namespace Modules\Tenant\Application\Services;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;
class TenantService implements TenantServiceInterface {
    public function __construct(private readonly TenantRepositoryInterface $repo) {}
    public function create(array $data): Tenant {
        $tenant = new Tenant(null,$data['name'],$data['slug'],$data['plan']??'free',true,$data['settings']??null,null,new \DateTimeImmutable());
        return $this->repo->save($tenant);
    }
    public function findById(int $id): ?Tenant { return $this->repo->findById($id); }
    public function findBySlug(string $slug): ?Tenant { return $this->repo->findBySlug($slug); }
    public function update(int $id, array $data): Tenant {
        $t = $this->repo->findById($id);
        if (!$t) throw new NotFoundException("Tenant {$id} not found");
        $updated = new Tenant($t->getId(),$data['name']??$t->getName(),$data['slug']??$t->getSlug(),$data['plan']??$t->getPlan(),$data['is_active']??$t->isActive(),$data['settings']??$t->getSettings(),$t->getTrialEndsAt(),$t->getCreatedAt());
        return $this->repo->save($updated);
    }
    public function delete(int $id): void { $this->repo->delete($id); }
}
