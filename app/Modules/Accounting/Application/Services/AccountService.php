<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\AccountServiceInterface;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class AccountService implements AccountServiceInterface
{
    public function __construct(
        private readonly AccountRepositoryInterface $repository,
    ) {}

    public function findById(int $id): Account
    {
        $account = $this->repository->findById($id);

        if ($account === null) {
            throw new NotFoundException('Account', $id);
        }

        return $account;
    }

    public function findByCode(int $tenantId, string $code): Account
    {
        $account = $this->repository->findByCode($tenantId, $code);

        if ($account === null) {
            throw new NotFoundException("Account with code '{$code}'");
        }

        return $account;
    }

    public function findByType(int $tenantId, string $type): array
    {
        return $this->repository->findByType($tenantId, $type);
    }

    public function all(int $tenantId): array
    {
        return $this->repository->all($tenantId);
    }

    public function getTree(int $tenantId): array
    {
        return $this->repository->getTree($tenantId);
    }

    public function create(array $data): Account
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Account
    {
        $account = $this->repository->update($id, $data);

        if ($account === null) {
            throw new NotFoundException('Account', $id);
        }

        return $account;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
