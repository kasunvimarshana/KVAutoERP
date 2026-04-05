<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Account;

interface AccountRepositoryInterface
{
    public function findById(string $id): ?Account;
    public function findByCode(string $code, string $tenantId): ?Account;
    public function allByTenant(string $tenantId): Collection;
    public function getByType(string $type, string $tenantId): Collection;
    public function getRoots(string $tenantId): Collection;
    public function getChildren(string $parentId): Collection;
    public function create(array $data): Account;
    public function update(string $id, array $data): Account;
    public function delete(string $id): bool;
}
