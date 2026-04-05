<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\RepositoryInterfaces;
use Modules\Accounting\Domain\Entities\TransactionRule;
interface TransactionRuleRepositoryInterface {
    public function findByTenant(int $tenantId): array;
    public function save(TransactionRule $rule): TransactionRule;
    public function delete(int $id): void;
}
