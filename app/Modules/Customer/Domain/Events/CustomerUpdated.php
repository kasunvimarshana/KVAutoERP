<?php declare(strict_types=1);
namespace Modules\Customer\Domain\Events;
use Modules\Core\Domain\Events\BaseEvent;
use Modules\Customer\Domain\Entities\Customer;
class CustomerUpdated extends BaseEvent {
    public Customer $customer;
    public function __construct(Customer $c) { parent::__construct($c->getTenantId()); $this->customer=$c; }
    public function broadcastWith(): array { return ['id'=>$this->customer->getId(),'name'=>$this->customer->getName(),'code'=>$this->customer->getCode(),'status'=>$this->customer->getStatus(),'tenantId'=>$this->tenantId]; }
}
