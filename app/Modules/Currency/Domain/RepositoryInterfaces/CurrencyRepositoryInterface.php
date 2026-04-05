<?php declare(strict_types=1);
namespace Modules\Currency\Domain\RepositoryInterfaces;
use Modules\Currency\Domain\Entities\Currency;
interface CurrencyRepositoryInterface {
    public function findById(int $id): ?Currency;
    public function findByCode(int $tenantId, string $code): ?Currency;
    public function findByTenant(int $tenantId): array;
    public function save(Currency $currency): Currency;
    public function delete(int $id): void;
}
