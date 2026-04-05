<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Services;

use Modules\Tax\Application\Contracts\TaxGroupServiceInterface;
use Modules\Tax\Domain\Entities\TaxGroup;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;

class TaxGroupService implements TaxGroupServiceInterface
{
    public function __construct(
        private readonly TaxGroupRepositoryInterface $repository,
    ) {}

    public function create(array $data): TaxGroup
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): TaxGroup
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    public function findById(int $id, int $tenantId): ?TaxGroup
    {
        return $this->repository->findById($id, $tenantId);
    }

    public function findByCode(string $code, int $tenantId): ?TaxGroup
    {
        return $this->repository->findByCode($code, $tenantId);
    }

    public function listAll(int $tenantId): array
    {
        return $this->repository->listAll($tenantId);
    }
}
