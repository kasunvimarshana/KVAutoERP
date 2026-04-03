<?php declare(strict_types=1);
namespace Modules\Supplier\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
use Modules\Supplier\Domain\Entities\Supplier;
use Modules\Supplier\Domain\RepositoryInterfaces\SupplierRepositoryInterface;
use Modules\Supplier\Infrastructure\Persistence\Eloquent\Models\SupplierModel;
class EloquentSupplierRepository extends EloquentRepository implements SupplierRepositoryInterface {
    public function __construct(SupplierModel $m) { parent::__construct($m); }
    public function findByCode(int $t, string $c): ?Supplier { return null; }
    public function findByTenant(int $t): Collection { return new Collection(); }
    public function findByUserId(int $u): Collection { return new Collection(); }
    public function save(Supplier $s): Supplier { return $s; }
}
