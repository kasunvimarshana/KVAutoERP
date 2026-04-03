<?php declare(strict_types=1);
namespace Modules\Customer\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Customer\Application\Contracts\UpdateCustomerServiceInterface;
use Modules\Customer\Domain\RepositoryInterfaces\CustomerRepositoryInterface;
class UpdateCustomerService extends BaseService implements UpdateCustomerServiceInterface {
    public function __construct(CustomerRepositoryInterface $r) { parent::__construct($r); }
    protected function handle(array $d): mixed { return null; }
}
