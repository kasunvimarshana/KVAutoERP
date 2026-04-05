<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Collection;
use Modules\Accounting\Application\Contracts\BankAccountServiceInterface;
use Modules\Accounting\Domain\Entities\BankAccount;
use Modules\Accounting\Domain\RepositoryInterfaces\BankAccountRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

final class BankAccountService implements BankAccountServiceInterface
{
    public function __construct(
        private readonly BankAccountRepositoryInterface $repository,
    ) {}

    public function findById(int $id): ?BankAccount
    {
        return $this->repository->findById($id);
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->repository->findByTenant($tenantId);
    }

    public function create(array $data): BankAccount
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): ?BankAccount
    {
        $account = $this->repository->findById($id);

        if ($account === null) {
            throw new NotFoundException("Bank account #{$id} not found.");
        }

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $account = $this->repository->findById($id);

        if ($account === null) {
            throw new NotFoundException("Bank account #{$id} not found.");
        }

        return $this->repository->delete($id);
    }

    public function updateBalance(int $bankAccountId, float $newBalance): ?BankAccount
    {
        $account = $this->repository->findById($bankAccountId);

        if ($account === null) {
            throw new NotFoundException("Bank account #{$bankAccountId} not found.");
        }

        return $this->repository->update($bankAccountId, ['current_balance' => $newBalance]);
    }
}
