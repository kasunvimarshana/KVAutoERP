<?php declare(strict_types=1);
namespace Modules\Customer\Domain\RepositoryInterfaces;
use Illuminate\Support\Collection;
use Modules\Core\Domain\Contracts\Repositories\RepositoryInterface;
use Modules\Customer\Domain\Entities\Customer;
interface CustomerRepositoryInterface extends RepositoryInterface {
    public function findByCode(int $t, string $c): ?Customer;
    public function findByTenant(int $t): Collection;
    public function findByUserId(int $u): Collection;
    public function save(Customer $c): Customer;
}
