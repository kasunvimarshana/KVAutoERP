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

    public function create(array $data): Currency
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Currency
    {
        return $this->repository->update($id, $data);
    }

    public function findById(int $id, int $tenantId): ?Currency
    {
        return $this->repository->findById($id, $tenantId);
    }

    public function findByCode(string $code, int $tenantId): ?Currency
    {
        return $this->repository->findByCode($code, $tenantId);
    }

    public function listAll(int $tenantId): array
    {
        return $this->repository->listAll($tenantId);
    }

    public function setDefault(int $id, int $tenantId): Currency
    {
        $currency = $this->repository->findById($id, $tenantId);

        if ($currency === null) {
            throw new NotFoundException("Currency #{$id} not found.");
        }

        $this->repository->clearDefault($tenantId);

        return $this->repository->update($id, ['is_default' => true]);
    }
}
