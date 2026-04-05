<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Services;

use Modules\Tax\Application\Contracts\TaxRateServiceInterface;
use Modules\Tax\Domain\Entities\TaxRate;
use Modules\Tax\Domain\RepositoryInterfaces\TaxRateRepositoryInterface;

class TaxRateService implements TaxRateServiceInterface
{
    public function __construct(
        private readonly TaxRateRepositoryInterface $repo,
    ) {}

    public function create(array $data): TaxRate
    {
        $tenantId = (int) $data['tenant_id'];
        $code     = (string) $data['code'];

        if ($this->repo->findByCode($code, $tenantId) !== null) {
            throw new \InvalidArgumentException("Tax rate code '{$code}' already exists for this tenant.");
        }

        if (!in_array($data['type'] ?? 'percentage', ['percentage', 'fixed'], true)) {
            throw new \InvalidArgumentException("Tax rate type must be 'percentage' or 'fixed'.");
        }

        if ((float) ($data['rate'] ?? 0) < 0) {
            throw new \InvalidArgumentException('Tax rate must be non-negative.');
        }

        return $this->repo->create($data);
    }

    public function update(int $id, array $data): TaxRate
    {
        $tenantId = (int) ($data['tenant_id'] ?? 0);
        $taxRate  = $this->repo->findById($id, $tenantId);

        if ($taxRate === null) {
            throw new \InvalidArgumentException("Tax rate with id {$id} not found.");
        }

        if (isset($data['code']) && $data['code'] !== $taxRate->code) {
            $existing = $this->repo->findByCode((string) $data['code'], $tenantId);
            if ($existing !== null && $existing->id !== $id) {
                throw new \InvalidArgumentException("Tax rate code '{$data['code']}' already exists for this tenant.");
            }
        }

        if (isset($data['type']) && !in_array($data['type'], ['percentage', 'fixed'], true)) {
            throw new \InvalidArgumentException("Tax rate type must be 'percentage' or 'fixed'.");
        }

        return $this->repo->update($id, $data);
    }

    public function delete(int $id, int $tenantId): bool
    {
        $taxRate = $this->repo->findById($id, $tenantId);

        if ($taxRate === null) {
            throw new \InvalidArgumentException("Tax rate with id {$id} not found.");
        }

        return $this->repo->delete($id, $tenantId);
    }

    public function findById(int $id, int $tenantId): TaxRate
    {
        $taxRate = $this->repo->findById($id, $tenantId);

        if ($taxRate === null) {
            throw new \InvalidArgumentException("Tax rate with id {$id} not found.");
        }

        return $taxRate;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->repo->allByTenant($tenantId);
    }

    public function getActive(int $tenantId): array
    {
        return $this->repo->findActive($tenantId);
    }

    public function getByCountry(string $country, int $tenantId): array
    {
        return $this->repo->findByCountry($country, $tenantId);
    }
}
