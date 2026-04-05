<?php
declare(strict_types=1);
namespace Modules\CRM\Application\Services;
use DateTimeImmutable;
use Modules\CRM\Application\Contracts\ActivityServiceInterface;
use Modules\CRM\Domain\Entities\Activity;
use Modules\CRM\Domain\RepositoryInterfaces\ActivityRepositoryInterface;
use Illuminate\Support\Collection;
class ActivityService implements ActivityServiceInterface {
    public function __construct(private readonly ActivityRepositoryInterface $repository) {}
    public function create(array $data): Activity {
        return $this->repository->create($data);
    }
    public function update(int $id, array $data): Activity {
        return $this->repository->update($id, $data);
    }
    public function delete(int $id): bool {
        return $this->repository->delete($id);
    }
    public function findById(int $id): ?Activity {
        return $this->repository->findById($id);
    }
    public function findByTenant(int $tenantId): Collection {
        return $this->repository->findByTenant($tenantId);
    }
    public function complete(int $id): Activity {
        return $this->repository->update($id, [
            'status'       => 'completed',
            'completed_at' => new DateTimeImmutable(),
        ]);
    }
    public function getForContact(int $contactId): Collection {
        return $this->repository->getForContact($contactId);
    }
    public function getForOpportunity(int $opportunityId): Collection {
        return $this->repository->getForOpportunity($opportunityId);
    }
}
