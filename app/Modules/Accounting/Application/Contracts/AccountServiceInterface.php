<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Modules\Accounting\Domain\Entities\Account;

interface AccountServiceInterface
{
    public function findById(int $id): Account;

    public function findByCode(int $tenantId, string $code): Account;

    /** @return Account[] */
    public function findByType(int $tenantId, string $type): array;

    /** @return Account[] */
    public function all(int $tenantId): array;

    public function getTree(int $tenantId): array;

    public function create(array $data): Account;

    public function update(int $id, array $data): Account;

    public function delete(int $id): bool;
}
