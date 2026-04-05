<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Modules\Accounting\Domain\Entities\TransactionRule;

interface TransactionRuleRepositoryInterface
{
    /** @return TransactionRule[] Sorted by priority ascending */
    public function findActive(int $tenantId): array;

    public function create(array $data): TransactionRule;

    public function update(int $id, array $data): ?TransactionRule;

    public function delete(int $id): bool;
}
