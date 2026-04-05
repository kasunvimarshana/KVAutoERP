<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Account;

interface AccountServiceInterface
{
    public function createAccount(array $data): Account;
    public function updateAccount(string $id, array $data): Account;
    public function getAccount(string $id): Account;
    public function getTree(string $tenantId): Collection;
    public function getByType(string $type, string $tenantId): Collection;
    public function getAll(string $tenantId): Collection;
}
