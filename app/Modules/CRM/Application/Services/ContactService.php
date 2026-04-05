<?php
declare(strict_types=1);
namespace Modules\CRM\Application\Services;
use Modules\CRM\Application\Contracts\ContactServiceInterface;
use Modules\CRM\Domain\Entities\Contact;
use Modules\CRM\Domain\RepositoryInterfaces\ContactRepositoryInterface;
use Illuminate\Support\Collection;
class ContactService implements ContactServiceInterface {
    public function __construct(private readonly ContactRepositoryInterface $repository) {}
    public function create(array $data): Contact {
        return $this->repository->create($data);
    }
    public function update(int $id, array $data): Contact {
        return $this->repository->update($id, $data);
    }
    public function delete(int $id): bool {
        return $this->repository->delete($id);
    }
    public function findById(int $id): ?Contact {
        return $this->repository->findById($id);
    }
    public function findByTenant(int $tenantId): Collection {
        return $this->repository->findByTenant($tenantId);
    }
    public function findByType(int $tenantId, string $type): Collection {
        return $this->repository->findByType($tenantId, $type);
    }
    public function findByEmail(string $email): ?Contact {
        return $this->repository->findByEmail($email);
    }
}
