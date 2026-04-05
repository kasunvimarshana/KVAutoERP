<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Services;

use Modules\Tax\Application\Contracts\TaxGroupServiceInterface;
use Modules\Tax\Domain\Entities\TaxGroup;
use Modules\Tax\Domain\Entities\TaxGroupRate;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRateRepositoryInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxRateRepositoryInterface;

class TaxGroupService implements TaxGroupServiceInterface
{
    public function __construct(
        private readonly TaxGroupRepositoryInterface $repo,
        private readonly TaxGroupRateRepositoryInterface $groupRateRepo,
        private readonly TaxRateRepositoryInterface $taxRateRepo,
    ) {}

    public function create(array $data): TaxGroup
    {
        $tenantId = (int) $data['tenant_id'];
        $code     = (string) $data['code'];

        if ($this->repo->findByCode($code, $tenantId) !== null) {
            throw new \InvalidArgumentException("Tax group code '{$code}' already exists for this tenant.");
        }

        return $this->repo->create($data);
    }

    public function update(int $id, array $data): TaxGroup
    {
        $tenantId = (int) ($data['tenant_id'] ?? 0);
        $group    = $this->repo->findById($id, $tenantId);

        if ($group === null) {
            throw new \InvalidArgumentException("Tax group with id {$id} not found.");
        }

        if (isset($data['code']) && $data['code'] !== $group->code) {
            $existing = $this->repo->findByCode((string) $data['code'], $tenantId);
            if ($existing !== null && $existing->id !== $id) {
                throw new \InvalidArgumentException("Tax group code '{$data['code']}' already exists for this tenant.");
            }
        }

        return $this->repo->update($id, $data);
    }

    public function delete(int $id, int $tenantId): bool
    {
        $group = $this->repo->findById($id, $tenantId);

        if ($group === null) {
            throw new \InvalidArgumentException("Tax group with id {$id} not found.");
        }

        $this->groupRateRepo->deleteByGroup($id, $tenantId);

        return $this->repo->delete($id, $tenantId);
    }

    public function findById(int $id, int $tenantId): TaxGroup
    {
        $group = $this->repo->findById($id, $tenantId);

        if ($group === null) {
            throw new \InvalidArgumentException("Tax group with id {$id} not found.");
        }

        return $group;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->repo->allByTenant($tenantId);
    }

    public function addRate(int $taxGroupId, int $taxRateId, int $sortOrder, int $tenantId): TaxGroupRate
    {
        $group = $this->repo->findById($taxGroupId, $tenantId);
        if ($group === null) {
            throw new \InvalidArgumentException("Tax group with id {$taxGroupId} not found.");
        }

        $rate = $this->taxRateRepo->findById($taxRateId, $tenantId);
        if ($rate === null) {
            throw new \InvalidArgumentException("Tax rate with id {$taxRateId} not found.");
        }

        return $this->groupRateRepo->create([
            'tenant_id'    => $tenantId,
            'tax_group_id' => $taxGroupId,
            'tax_rate_id'  => $taxRateId,
            'sort_order'   => $sortOrder,
        ]);
    }

    public function removeRate(int $taxGroupRateId, int $tenantId): bool
    {
        $groupRate = $this->groupRateRepo->findById($taxGroupRateId, $tenantId);

        if ($groupRate === null) {
            throw new \InvalidArgumentException("Tax group rate with id {$taxGroupRateId} not found.");
        }

        return $this->groupRateRepo->delete($taxGroupRateId, $tenantId);
    }

    public function getRates(int $taxGroupId, int $tenantId): array
    {
        return $this->groupRateRepo->findByGroup($taxGroupId, $tenantId);
    }
}
