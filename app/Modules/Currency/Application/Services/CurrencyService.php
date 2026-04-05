<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Currency\Application\Contracts\CurrencyServiceInterface;
use Modules\Currency\Domain\Entities\Currency;
use Modules\Currency\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;

class CurrencyService implements CurrencyServiceInterface
{
    public function __construct(
        private readonly CurrencyRepositoryInterface $repository,
    ) {}

    public function findById(int $id): Currency
    {
        $currency = $this->repository->findById($id);

        if ($currency === null) {
            throw new NotFoundException('Currency', $id);
        }

        return $currency;
    }

    public function findByCode(int $tenantId, string $code): Currency
    {
        $currency = $this->repository->findByCode($tenantId, $code);

        if ($currency === null) {
            throw new NotFoundException("Currency with code '{$code}'");
        }

        return $currency;
    }

    public function findDefault(int $tenantId): Currency
    {
        $currency = $this->repository->findDefault($tenantId);

        if ($currency === null) {
            throw new NotFoundException('Default currency');
        }

        return $currency;
    }

    public function all(int $tenantId): array
    {
        return $this->repository->all($tenantId);
    }

    public function create(array $data): Currency
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Currency
    {
        $currency = $this->repository->update($id, $data);

        if ($currency === null) {
            throw new NotFoundException('Currency', $id);
        }

        return $currency;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function setDefault(int $tenantId, string $code): Currency
    {
        $currency = $this->repository->findByCode($tenantId, $code);

        if ($currency === null) {
            throw new NotFoundException("Currency with code '{$code}'");
        }

        // Clear existing default for tenant
        $existing = $this->repository->findDefault($tenantId);
        if ($existing !== null && $existing->getId() !== $currency->getId()) {
            $this->repository->update($existing->getId(), ['is_default' => false]);
        }

        return $this->repository->update($currency->getId(), ['is_default' => true]);
    }
}
