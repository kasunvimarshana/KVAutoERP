<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\TransactionRule;

interface TransactionRuleRepositoryInterface
{
    public function findById(string $id): ?TransactionRule;
    public function allByTenant(string $tenantId): Collection;
    public function getActive(string $tenantId): Collection;
    public function create(array $data): TransactionRule;
    public function update(string $id, array $data): TransactionRule;
    public function delete(string $id): bool;
}
