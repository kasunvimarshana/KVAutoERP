<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Modules\Pricing\Application\Contracts\PriceListServiceInterface;
use Modules\Pricing\Domain\Entities\PriceList;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListRepositoryInterface;

class PriceListService implements PriceListServiceInterface
{
    public function __construct(
        private readonly PriceListRepositoryInterface $repo,
    ) {}

    public function create(array $data): PriceList
    {
        $tenantId = (int) $data['tenant_id'];
        $code     = (string) $data['code'];

        if ($this->repo->findByCode($code, $tenantId) !== null) {
            throw new \InvalidArgumentException("Price list code '{$code}' already exists for this tenant.");
        }

        return $this->repo->create($data);
    }

    public function update(int $id, array $data): PriceList
    {
        $tenantId  = (int) ($data['tenant_id'] ?? 0);
        $priceList = $this->repo->findById($id, $tenantId);

        if ($priceList === null) {
            throw new \InvalidArgumentException("Price list with id {$id} not found.");
        }

        if (isset($data['code']) && $data['code'] !== $priceList->code) {
            $existing = $this->repo->findByCode((string) $data['code'], $tenantId);
            if ($existing !== null && $existing->id !== $id) {
                throw new \InvalidArgumentException("Price list code '{$data['code']}' already exists for this tenant.");
            }
        }

        return $this->repo->update($id, $data);
    }

    public function delete(int $id, int $tenantId): bool
    {
        $priceList = $this->repo->findById($id, $tenantId);

        if ($priceList === null) {
            throw new \InvalidArgumentException("Price list with id {$id} not found.");
        }

        return $this->repo->delete($id, $tenantId);
    }

    public function findById(int $id, int $tenantId): PriceList
    {
        $priceList = $this->repo->findById($id, $tenantId);

        if ($priceList === null) {
            throw new \InvalidArgumentException("Price list with id {$id} not found.");
        }

        return $priceList;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->repo->allByTenant($tenantId);
    }

    public function getDefault(int $tenantId): ?PriceList
    {
        return $this->repo->findDefault($tenantId);
    }

    public function setDefault(int $id, int $tenantId): PriceList
    {
        $priceList = $this->repo->findById($id, $tenantId);

        if ($priceList === null) {
            throw new \InvalidArgumentException("Price list with id {$id} not found.");
        }

        // Unset current default
        $current = $this->repo->findDefault($tenantId);
        if ($current !== null && $current->id !== $id) {
            $this->repo->update($current->id, ['is_default' => false]);
        }

        return $this->repo->update($id, ['is_default' => true]);
    }
}
