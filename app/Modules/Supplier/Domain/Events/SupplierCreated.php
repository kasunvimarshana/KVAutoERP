<?php
declare(strict_types=1);
namespace Modules\Supplier\Domain\Events;
use Modules\Core\Domain\Events\BaseEvent;
use Modules\Supplier\Domain\Entities\Supplier;
class SupplierCreated extends BaseEvent
{
    public Supplier $supplier;
    public function __construct(Supplier $supplier) { parent::__construct($supplier->getTenantId()); $this->supplier = $supplier; }
    public function broadcastWith(): array { return ['id' => $this->supplier->getId(), 'name' => $this->supplier->getName(), 'code' => $this->supplier->getCode(), 'status' => $this->supplier->getStatus(), 'tenantId' => $this->tenantId]; }
}
