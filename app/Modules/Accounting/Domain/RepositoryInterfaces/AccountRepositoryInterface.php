<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\RepositoryInterfaces;
use Modules\Accounting\Domain\Entities\Account;
interface AccountRepositoryInterface {
    public function findById(int $id): ?Account;
    public function findByCode(int $tenantId, string $code): ?Account;
    public function findByTenant(int $tenantId): array;
    public function findByType(int $tenantId, string $type): array;
    public function save(Account $account): Account;
    public function delete(int $id): void;
}
