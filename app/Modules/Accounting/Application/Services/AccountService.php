<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Collection;
use Modules\Accounting\Application\Contracts\AccountServiceInterface;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class AccountService implements AccountServiceInterface
{
    public function __construct(
        private readonly AccountRepositoryInterface $repository,
    ) {}

    public function createAccount(array $data): Account
    {
        if (! empty($data['code'])) {
            $existing = $this->repository->findByCode($data['code'], $data['tenant_id']);
            if ($existing) {
                throw new DomainException("Account code '{$data['code']}' already exists.");
            }
        }

        $data['is_active']       = $data['is_active'] ?? true;
        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['current_balance'] = $data['current_balance'] ?? $data['opening_balance'];
        $data['currency']        = $data['currency'] ?? 'USD';

        return $this->repository->create($data);
    }

    public function updateAccount(string $id, array $data): Account
    {
        $this->getAccount($id);
        return $this->repository->update($id, $data);
    }

    public function getAccount(string $id): Account
    {
        $account = $this->repository->findById($id);
        if (! $account) {
            throw new NotFoundException('Account', $id);
        }
        return $account;
    }

    public function getTree(string $tenantId): Collection
    {
        return $this->repository->getRoots($tenantId);
    }

    public function getByType(string $type, string $tenantId): Collection
    {
        return $this->repository->getByType($type, $tenantId);
    }

    public function getAll(string $tenantId): Collection
    {
        return $this->repository->allByTenant($tenantId);
    }
}
