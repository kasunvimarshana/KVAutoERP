<?php declare(strict_types=1);
namespace Modules\Customer\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Customer\Application\Contracts\DeleteCustomerServiceInterface;
use Modules\Customer\Domain\RepositoryInterfaces\CustomerRepositoryInterface;
class DeleteCustomerService extends BaseService implements DeleteCustomerServiceInterface {
    public function __construct(CustomerRepositoryInterface $r) { parent::__construct($r); }
    protected function handle(array $d): bool { return $this->repository->delete($d['id']); }
}
