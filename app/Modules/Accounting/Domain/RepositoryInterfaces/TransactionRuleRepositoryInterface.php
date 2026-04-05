<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\TransactionRule;

interface TransactionRuleRepositoryInterface
{
    public function findById(int $id): ?TransactionRule;

    /** @return Collection<int, TransactionRule> */
    public function findByTenant(int $tenantId): Collection;

    /**
     * Returns active rules ordered by priority descending.
     *
     * @return Collection<int, TransactionRule>
     */
    public function findActive(int $tenantId): Collection;

    public function create(array $data): TransactionRule;

    public function update(int $id, array $data): ?TransactionRule;

    public function delete(int $id): bool;
}
