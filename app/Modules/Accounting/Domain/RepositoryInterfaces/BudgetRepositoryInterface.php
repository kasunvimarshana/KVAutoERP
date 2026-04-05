<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Budget;

interface BudgetRepositoryInterface
{
    public function findById(string $id): ?Budget;
    public function allByTenant(string $tenantId): Collection;
    public function create(array $data): Budget;
    public function update(string $id, array $data): Budget;
    public function delete(string $id): bool;
}
