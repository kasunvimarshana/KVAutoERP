<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Modules\Accounting\Domain\Entities\Account;

interface AccountRepositoryInterface
{
    public function findById(int $id): ?Account;

    public function findByCode(int $tenantId, string $code): ?Account;

    /** @return Account[] */
    public function findByType(int $tenantId, string $type): array;

    public function create(array $data): Account;

    public function update(int $id, array $data): ?Account;

    public function delete(int $id): bool;

    /** @return Account[] */
    public function all(int $tenantId): array;

    /**
     * Returns nested tree structure (each Account with optional 'children' key).
     * @return array<int, array{account: Account, children: array}>
     */
    public function getTree(int $tenantId): array;
}
