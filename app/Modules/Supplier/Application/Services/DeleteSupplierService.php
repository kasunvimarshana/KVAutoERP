<?php declare(strict_types=1);
namespace Modules\Supplier\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Supplier\Application\Contracts\DeleteSupplierServiceInterface;
use Modules\Supplier\Domain\RepositoryInterfaces\SupplierRepositoryInterface;
class DeleteSupplierService extends BaseService implements DeleteSupplierServiceInterface {
    public function __construct(SupplierRepositoryInterface $r) { parent::__construct($r); }
    protected function handle(array $d): bool { return $this->repository->delete($d['id']); }
}
