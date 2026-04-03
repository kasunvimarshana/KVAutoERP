<?php declare(strict_types=1);
namespace Modules\Customer\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
use Modules\Customer\Domain\Entities\Customer;
use Modules\Customer\Domain\RepositoryInterfaces\CustomerRepositoryInterface;
use Modules\Customer\Infrastructure\Persistence\Eloquent\Models\CustomerModel;
class EloquentCustomerRepository extends EloquentRepository implements CustomerRepositoryInterface {
    public function __construct(CustomerModel $m) { parent::__construct($m); }
    public function findByCode(int $t, string $c): ?Customer { return null; }
    public function findByTenant(int $t): Collection { return new Collection(); }
    public function findByUserId(int $u): Collection { return new Collection(); }
    public function save(Customer $c): Customer { return $c; }
}
