<?php declare(strict_types=1);
namespace Modules\Customer\Domain\Events;
use Modules\Core\Domain\Events\BaseEvent;
class CustomerDeleted extends BaseEvent {
    public int $customerId;
    public function __construct(int $customerId, int $tenantId) { parent::__construct($tenantId); $this->customerId=$customerId; }
    public function broadcastWith(): array { return ['customerId'=>$this->customerId,'tenantId'=>$this->tenantId]; }
}
