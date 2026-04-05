<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\ExpenseCategory;

interface ExpenseCategoryRepositoryInterface
{
    public function findById(string $id): ?ExpenseCategory;
    public function findByCode(string $code, string $tenantId): ?ExpenseCategory;
    public function allByTenant(string $tenantId): Collection;
    public function create(array $data): ExpenseCategory;
    public function update(string $id, array $data): ExpenseCategory;
    public function delete(string $id): bool;
}
