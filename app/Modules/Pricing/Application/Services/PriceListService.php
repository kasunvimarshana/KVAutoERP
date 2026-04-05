<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Pricing\Application\Contracts\PriceListServiceInterface;
use Modules\Pricing\Domain\Entities\PriceList;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListRepositoryInterface;

class PriceListService implements PriceListServiceInterface
{
    public function __construct(
        private readonly PriceListRepositoryInterface $repository,
    ) {}

    public function create(array $data): PriceList
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): PriceList
    {
        $priceList = $this->repository->update($id, $data);

        if ($priceList === null) {
            throw new NotFoundException('PriceList', $id);
        }

        return $priceList;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): PriceList
    {
        $priceList = $this->repository->findById($id);

        if ($priceList === null) {
            throw new NotFoundException('PriceList', $id);
        }

        return $priceList;
    }

    public function setDefault(int $tenantId, string $code): PriceList
    {
        $priceList = $this->repository->findByCode($tenantId, $code);

        if ($priceList === null) {
            throw new NotFoundException("PriceList with code '{$code}'");
        }

        // Unset all other defaults for the tenant first
        $active = $this->repository->findActive($tenantId);
        foreach ($active as $item) {
            if ($item->isDefault() && $item->getId() !== $priceList->getId()) {
                $this->repository->update($item->getId(), ['is_default' => false]);
            }
        }

        return $this->update($priceList->getId(), ['is_default' => true]);
    }
}
