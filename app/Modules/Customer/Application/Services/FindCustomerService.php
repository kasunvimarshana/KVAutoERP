<?php declare(strict_types=1);
namespace Modules\Customer\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Customer\Application\Contracts\FindCustomerServiceInterface;
use Modules\Customer\Domain\RepositoryInterfaces\CustomerRepositoryInterface;
class FindCustomerService extends BaseService implements FindCustomerServiceInterface {
    public function __construct(CustomerRepositoryInterface $r) { parent::__construct($r); }
    protected function handle(array $d): mixed { throw new \BadMethodCallException('Read-only service'); }
}
