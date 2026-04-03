<?php declare(strict_types=1);
namespace Modules\Supplier\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Supplier\Application\Contracts\CreateSupplierServiceInterface;
use Modules\Supplier\Domain\Entities\Supplier;
use Modules\Supplier\Domain\RepositoryInterfaces\SupplierRepositoryInterface;
class CreateSupplierService extends BaseService implements CreateSupplierServiceInterface {
    public function __construct(SupplierRepositoryInterface $r) { parent::__construct($r); }
    protected function handle(array $d): Supplier { $s=new Supplier($d['tenant_id'],$d['name'],$d['code'],$d['user_id']??null,$d['email']??null,$d['phone']??null,$d['address']??null,$d['contact_person']??null,$d['payment_terms']??null,$d['currency']??'USD',$d['tax_number']??null,$d['status']??'active',$d['type']??'other',$d['attributes']??null,$d['metadata']??null); return $this->repository->save($s); }
}
