<?php declare(strict_types=1);
namespace Modules\Customer\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Customer\Application\Contracts\CreateCustomerServiceInterface;
use Modules\Customer\Domain\Entities\Customer;
use Modules\Customer\Domain\RepositoryInterfaces\CustomerRepositoryInterface;
class CreateCustomerService extends BaseService implements CreateCustomerServiceInterface {
    public function __construct(CustomerRepositoryInterface $r) { parent::__construct($r); }
    protected function handle(array $d): Customer { $c=new Customer($d['tenant_id'],$d['name'],$d['code'],$d['user_id']??null,$d['email']??null,$d['phone']??null,$d['billing_address']??null,$d['shipping_address']??null,$d['date_of_birth']??null,$d['loyalty_tier']??null,$d['credit_limit']??null,$d['payment_terms']??null,$d['currency']??'USD',$d['tax_number']??null,$d['status']??'active',$d['type']??'retail',$d['attributes']??null,$d['metadata']??null); return $this->repository->save($c); }
}
