<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Tax\Application\Contracts\TaxGroupServiceInterface;
use Modules\Tax\Domain\Entities\TaxGroup;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;

class TaxGroupService implements TaxGroupServiceInterface
{
    public function __construct(
        private readonly TaxGroupRepositoryInterface $repository,
    ) {}

    public function findById(int $id): TaxGroup
    {
        $group = $this->repository->findById($id);

        if ($group === null) {
            throw new NotFoundException('TaxGroup', $id);
        }

        return $group;
    }

    public function findByCode(int $tenantId, string $code): TaxGroup
    {
        $group = $this->repository->findByCode($tenantId, $code);

        if ($group === null) {
            throw new NotFoundException("TaxGroup with code '{$code}'");
        }

        return $group;
    }

    public function all(int $tenantId): array
    {
        return $this->repository->all($tenantId);
    }

    public function create(array $data): TaxGroup
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): TaxGroup
    {
        $group = $this->repository->update($id, $data);

        if ($group === null) {
            throw new NotFoundException('TaxGroup', $id);
        }

        return $group;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
