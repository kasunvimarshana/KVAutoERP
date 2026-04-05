<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Account;

interface AccountRepositoryInterface
{
    public function findById(int $id): ?Account;

    public function findByCode(int $tenantId, string $code): ?Account;

    /** @return Collection<int, Account> */
    public function findByType(int $tenantId, string $type): Collection;

    /** @return Collection<int, Account> */
    public function findByTenant(int $tenantId): Collection;

    /**
     * Returns a flat collection ordered by code, suitable for tree-building client-side.
     *
     * @return Collection<int, Account>
     */
    public function getTree(int $tenantId): Collection;

    public function create(array $data): Account;

    public function update(int $id, array $data): ?Account;

    public function delete(int $id): bool;
}
