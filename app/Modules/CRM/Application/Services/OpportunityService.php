<?php
declare(strict_types=1);
namespace Modules\CRM\Application\Services;
use Modules\CRM\Application\Contracts\OpportunityServiceInterface;
use Modules\CRM\Domain\Entities\Opportunity;
use Modules\CRM\Domain\RepositoryInterfaces\OpportunityRepositoryInterface;
use Illuminate\Support\Collection;
class OpportunityService implements OpportunityServiceInterface {
    public function __construct(private readonly OpportunityRepositoryInterface $repository) {}
    public function create(array $data): Opportunity {
        return $this->repository->create($data);
    }
    public function update(int $id, array $data): Opportunity {
        return $this->repository->update($id, $data);
    }
    public function delete(int $id): bool {
        return $this->repository->delete($id);
    }
    public function findById(int $id): ?Opportunity {
        return $this->repository->findById($id);
    }
    public function findByTenant(int $tenantId): Collection {
        return $this->repository->findByTenant($tenantId);
    }
    public function updateStage(int $id, string $stage): Opportunity {
        return $this->repository->update($id, ['stage' => $stage]);
    }
    public function markWon(int $id): Opportunity {
        return $this->repository->update($id, ['stage' => 'won', 'probability' => 100]);
    }
    public function markLost(int $id, string $reason): Opportunity {
        return $this->repository->update($id, ['stage' => 'lost', 'lost_reason' => $reason]);
    }
}
