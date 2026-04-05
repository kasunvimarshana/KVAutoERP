<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Collection;
use Modules\Configuration\Application\Contracts\CurrencyServiceInterface;
use Modules\Configuration\Domain\Entities\Currency;
use Modules\Configuration\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

final class CurrencyService implements CurrencyServiceInterface
{
    public function __construct(
        private readonly CurrencyRepositoryInterface $repository,
    ) {}

    public function findById(int $id): ?Currency
    {
        return $this->repository->findById($id);
    }

    public function findByCode(?int $tenantId, string $code): ?Currency
    {
        return $this->repository->findByCode($tenantId, $code);
    }

    public function getDefault(?int $tenantId): ?Currency
    {
        return $this->repository->findDefault($tenantId);
    }

    public function getActive(?int $tenantId): Collection
    {
        return $this->repository->findActive($tenantId);
    }

    public function create(array $data): Currency
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): ?Currency
    {
        $currency = $this->repository->findById($id);

        if ($currency === null) {
            throw new NotFoundException("Currency with ID {$id} not found.");
        }

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $currency = $this->repository->findById($id);

        if ($currency === null) {
            throw new NotFoundException("Currency with ID {$id} not found.");
        }

        return $this->repository->delete($id);
    }

    public function setDefault(?int $tenantId, int $currencyId): Currency
    {
        $currency = $this->repository->findById($currencyId);

        if ($currency === null) {
            throw new NotFoundException("Currency with ID {$currencyId} not found.");
        }

        if ($currency->tenantId !== $tenantId) {
            throw new NotFoundException("Currency with ID {$currencyId} not found.");
        }

        // Unset current default for this tenant scope
        $current = $this->repository->findDefault($tenantId);
        if ($current !== null && $current->id !== $currencyId) {
            $this->repository->update($current->id, ['is_default' => false]);
        }

        return $this->repository->update($currencyId, ['is_default' => true]);
    }
}
